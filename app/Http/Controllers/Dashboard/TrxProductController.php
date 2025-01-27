<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Bank;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\TrxProduct;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class TrxProductController extends Controller
{
    public function index()
    {
        $title = "Transaksi Produk";
        $categories = ProductCategory::all();
        $user = Auth::user();
        $pageUrl = "pages.dashboard.admin.trx-product";
        $banks = Bank::all();

        if ($user->role == "RESELLER") {
            $pageUrl = "pages.dashboard.reseller.trx-product";
        }

        return view($pageUrl, compact("title", "categories", "banks"));
    }

    // API
    public function dataTable(Request $request)
    {

        try {

            $query = TrxProduct::with([
                "Product" => function ($query) {
                    $query->select("id", "title", "code")
                        ->with("ProductCategory:id,title");
                },
                "User" => function ($query) {
                    $query->select("id", "code", "name");
                },
                "Bank" => function ($query) {
                    $query->select("id", "title", "account");
                }
            ]);

            // filter by reseller ID
            $user = auth()->user();
            if ($user->role == "RESELLER") {
                $query->where('user_id', $user->id);
            }

            // filter code trx
            if ($request->query("search")) {
                $searchValue = $request->query("search")['value'];
                $query->where('core', 'like', '%' . $searchValue . '%');
            }

            // filter status
            if ($request->query('status') && $request->query('status') != '') {
                $query->where('status', strtoupper($request->query('status')));
            }

            // filter payment type
            if ($request->query('payment_type') && $request->query('payment_type') != '') {
                $query->where("payment_type", strtoupper($request->query("payment_type")));
            }

            // filter kategori product
            if ($request->query('product_category_id') && $request->query('product_category_id') != "") {
                $productCategoryId = $request->query('product_category_id');
                $query->whereHas('Product.ProductCategory', function ($query) use ($productCategoryId) {
                    $query->where('id', $productCategoryId);
                });
            }

            // filter tanggal awal - tanggal akhir per bulan saat ini
            $tglAwal = $request->query('tgll_awal');
            $tglAkhir = $request->query('tgl_akhir');

            if (!$tglAwal) {
                $tglAwal = Carbon::now()->startOfDay()->utc();
            }

            if (!$tglAkhir) {
                $tglAkhir = Carbon::now()->endOfDay()->utc();
            }

            if ($request->query('tgl_awal') && $request->query('tgl_akhir')) {
                $tglAwal = Carbon::createFromDate('d/m/Y', $request->query('tgl_awal'))->startOfDay()->utc();
                $tglAkhir = Carbon::createFromDate('d/m/Y', $request->query('tgl_akhir'))->endOfDay()->utc();
            }

            $query->whereBetween('created_at', [$tglAwal, $tglAkhir]);
            $recordsFiltered = $query->count();
            $data = $query->orderBy('id', 'desc')
                ->skip($request->query('start'))
                ->limit($request->query('length'))
                ->get();

            $output = $data->map(function ($item) use ($user) {
                if ($user->role == "ADMIN") {
                    $action_process = $item->status == "PENDING" ? "<a class='dropdown-item' onclick='return changeStatus(\"{$item->id}\", 'PROCESS');' href='javascript:void(0);' title='In Process'>In Process</a>" : "";
                    $action_success = $item->status == "PENDING" || $item->status == "PROCESS" ? "<a class='dropdown-item' onclick='return changeStatus(\"{$item->id}\", 'SUCCESS');' href='javascript:void(0);' title='Success'>Success</a>" : "";
                    $action_reject = $item->status == "PENDING" || $item->status == "PROCESS" ? "<a class='dropdown-item' onclick='return changeStatus(\"{$item->id}\", 'REJECT');' href='javascript:void(0);' title='Reject'>Reject</a>" : "";
                    $action_print = $item->status == "SUCCESS" ? "<a class='dropdown-item' onclick='return printPreview(\"{$item->id}\");' href='javascript:void(0);' title='Print Preview'>Print Preview</a>" : "";
                    $action_show_proof_of_return = $item->status == "REJECT" && $item->payment_type == "TRANSFER" ? "<a class='dropdown-item' onclick='return showProofOfReturn(\"{$item->id}\");' href='javascript:void(0);' title='Bukti Refund'>Bukti Refund</a>" : "";

                    $action = " <div class='dropdown-primary dropdown open'>
                                <button class='btn btn-sm btn-primary dropdown-toggle waves-effect waves-light' id='dropdown-{$item->id}' data-toggle='dropdown' aria-haspopup='true' aria-expanded='true'>
                                    Aksi
                                </button>
                                <div class='dropdown-menu' aria-labelledby='dropdown-{$item->id}' data-dropdown-out='fadeOut'>
                                    <a class='dropdown-item' onclick='return getData(\"{$item->id}\");' href='javascript:void(0);' title='Detail'>Detail</a>
                                    " . $action_process . "
                                    " . $action_success . "
                                    " . $action_reject . "
                                    " . $action_print . "
                                    " . $action_show_proof_of_return . "
                                </div>
                            </div>";

                    $item["action"] = $action;
                    $item['profit'] = "<strong> : Rp. " . number_format($item->profit, 0, ',', '.') . "</strong>";
                } else {
                    $action_cancel = $item->status == "PENDING" ? "<a class='dropdown-item' onclick='return changeStatus(\"{$item->id}\", 'CANCEL');' href='javascript:void(0);' title='Cancel'>Cancel</a>" : "";
                    $action_print = $item->status == "SUCCESS" ? "<a class='dropdown-item' onclick='return printPreview(\"{$item->id}\");' href='javascript:void(0);' title='Print Preview'>Print Preview</a>" : "";
                    $action_show_proof_of_return = $item->status == "REJECT" && $item->payment_type == "TRANSFER" ? "<a class='dropdown-item' onclick='return showProofOfReturn(\"{$item->id}\");' href='javascript:void(0);' title='Bukti Refund'>Bukti Refund</a>" : "";

                    $action = " <div class='dropdown-primary dropdown open'>
                                <button class='btn btn-sm btn-primary dropdown-toggle waves-effect waves-light' id='dropdown-{$item->id}' data-toggle='dropdown' aria-haspopup='true' aria-expanded='true'>
                                    Aksi
                                </button>
                                <div class='dropdown-menu' aria-labelledby='dropdown-{$item->id}' data-dropdown-out='fadeOut'>
                                    <a class='dropdown-item' onclick='return getData(\"{$item->id}\");' href='javascript:void(0);' title='Detail'>Detail</a>
                                    " . $action_cancel . "
                                    " . $action_print . "
                                    " . $action_show_proof_of_return . "
                                </div>
                            </div>";

                    $item["action"] = $action;
                    unset($item['profit']); // profit hanya boleh dilihat admin
                }

                $product = "<small> 
                                <strong>Judul</strong> :" . ($item->Product ? $item->Product->title : 'Product Deleted') .  "
                                <br>
                                <strong>Code</strong> :" . ($item->Product ? $item->Product->code : 'Product Deleted') . "
                                <br>
                                <strong>Kategori</strong> : " . ($item->Product ? ($item->Product->ProductCategory ? $item->Product->ProductCategory->title : 'Product Deleted') : 'Product Deleted') . "
                                <br>
                            </small>";

                $reseller = "<small> 
                                <strong>Nama</strong> :" . ($item->User ? $item->User->title : 'Reseller Deleted') .  "
                                <br>
                                <strong>Code</strong> :" . ($item->User ? $item->User->code : 'Reseller Deleted') . "
                                <br>
                            </small>";


                $item['product'] = $product;
                $item['reseller'] = $reseller;
                $item['amount'] = "<strong> : Rp. " . number_format($item->amount, 0, ',', '.') . "</strong>";
                $item['total_amount'] = "<strong> : Rp. " . number_format($item->total_amount, 0, ',', '.') . "</strong>";
                $item['commission'] = "<strong> : Rp. " . number_format($item->commission, 0, ',', '.') . "</strong>";
                $item['payment_type'] = $item['payment_type'] == "TRANSFER" ? "TRANSFER BANK" : "PIHUTANG";

                unset($item['Product']);
                unset($item['User']);
                unset($item['Bank']);
            });

            $queryTotal = TrxProduct::count();
            if ($user->role == "RESELLER") {
                $query->where('user_id', $user->id);
            }

            $total = $queryTotal->count();
            return response()->json([
                'draw' => $request->query('draw'),
                'recordsFiltered' => $recordsFiltered,
                'recordsTotal' => $total,
                'data' => $output,
            ]);
        } catch (\Throwable $err) {
            return response()->json([
                "status" => "error",
                "message" => $err->getMessage(),
                'draw' => $request->query('draw'),
                'recordsFiltered' => 0,
                'recordsTotal' => 0,
                'data' => [],
            ], 500);
        }
    }

    public function create(Request $request)
    {
        try {
            DB::beginTransaction();
            $data = $request->all();
            $rules = [
                "product_id" => "required|integer",
                "qty" => "required|integer|min:1",
                "payment_type" => "required|string|in:TRANSFER,DEBT"
            ];

            $messages = [
                "product_id.required" => "Data Produk harus dipilih",
                "product_id.integer" => "Data Produk tidak valid",
                "qty.required" => "Jumlah Pesanan harus diisi",
                "qty.min" => "Jumlah pesanan minimal 1 Produk",
                "payment_type.required" => "Metode Bayar harus diisi",
                "payment_type.in" => "Metode Bayar tidak valid"
            ];

            if ($data["payment_type"] == "TRANSFER") {
                $rules["bank_id"] = "required|integer";
                $rules["proof_of_payment"] = "required|image|max:2048|mimes:giv,svg,jpeg,png,jpg";

                $messages["bank_id.requied"] = "Bank Pembayaran harus dipilih";
                $messages["bank_id.integer"] = "Bank Pambayaran tidak valid";
                $messages["proof_of_payment.required"] = "Bukti pembayaran harus diisi";
                $messages["proof_of_payment.image"] = "Bukti pembayaran tidak valid";
                $messages["proof_of_payment.max"] = "Bukti pembayaran maximal 2MB";
                $messages["proof_of_payment.mimes"] = "Format Bukti pembayaran harus giv/svg/jpeg/png/jpg";
            }

            $validator = Validator::make($data, $rules, $messages);
            if ($validator->fails()) {
                return response()->json([
                    "status" => "error",
                    "message" => $validator->errors()->first(),
                ], 400);
            }

            // CEK DATA PRODUK, STATUS PRODUK, STATUS KATEGORI DAN STOCK PRODUK
            $product = Product::with("ProductCategory")->where("id", $data["product_id"])->first();
            if (!$product) {
                return response()->json([
                    "status" => "error",
                    "message" => "Data Produk tidak ditemukan",
                ], 404);
            }

            if ($product->is_active == "N") {
                return response()->json([
                    "status" => "error",
                    "message" => "Data Produk tidak aktif",
                ], 400);
            }

            if ($product->stock <= 0 || $product->stock < $data["qty"]) {
                return response()->json([
                    "status" => "error",
                    "message" => "Stok Produk tidak mencukupi jumlah pesanan anda",
                ], 400);
            }

            if ($product->ProductCategory && $product->ProductCategory->is_active == "N") {
                return response()->json([
                    "status" => "error",
                    "message" => "Kategori Produk yang anda pilih tidak aktif",
                ], 400);
            }


            // KAKULASI HARGA PRODUK DAN PROFIT
            $user = User::find(auth()->user()->id);
            $purchasePrice = $product->purchase_price; // harga beli
            $sellingPrice = $product->selling_price; // harga jual
            $profitPerProduct = $sellingPrice - $purchasePrice; // keuntungan tiap produk berdasarkan harga jual - harga beli
            $commissionPerProduct = $user->level == "REGULAR" ? $product->commission_regular : $product->commission_vip; // komisi per produk

            $amount = $sellingPrice; // harga jual satuan
            $qty = $data["qty"]; // quantity pesanan
            $totalAmount = $amount * $qty; // total amount = harga jual * quantity
            $totalCommission = $commissionPerProduct * $qty; // total komisi = komisi per produk * quantity
            $totalProfit = ($profitPerProduct * $qty) - $totalCommission; // total profit = (profit per produk * quantity) - total komisi

            // PAYLOAD DATA
            $data["code"] = strtoupper(Str::random(10));
            $data["amount"] = $amount;
            $data["commission"] = $totalCommission;
            $data["qty"] = $qty;
            $data["total_amount"] = $totalAmount;
            $data["profit"] = $totalProfit;
            $data["status"] = "PENDING";
            $data["user_id"] = $user->id;

            // CEK TIPE BAYAR DEBT
            if ($data["payment_type"] == "DEBT") {
                // CEK LIMIT DEBT
                $limitDebt = $user->debt_limit;
                $totalDebt = $user->total_debt;
                $allowedDebt = $limitDebt - $totalDebt; // sisa pihutang yg masih bisa dipakai

                // jika jumlah pesanan melebihi limit pihutang
                if ($limitDebt < $totalAmount) {
                    return response()->json([
                        "status" => "error",
                        "message" => "Limit Pihutang anda tidak mencukupi untuk transaksi ini, silahkan hubungi admin",
                    ], 400);
                }

                // jika sisa pihutang tidak cukup
                if ($allowedDebt < $totalAmount) {
                    return response()->json([
                        "status" => "error",
                        "message" => "Total Pihutang anda sudah terlalu banyak untuk melanjutkan transaksi ini",
                    ], 400);
                }

                // UPDATE TOTAL_DEBT RESELLER
                $updateUser["total_debt"] = $totalDebt + $totalAmount;
                $user->update($updateUser);
            } else {
                // CEK TIPE BAYAR TF
                // CEK DATA BANK
                $bank = Bank::find($data["bank_id"]);
                if (!$bank) {
                    return response()->json([
                        "status" => "error",
                        "message" => "Data Bank Pembayaran tidak ditemukan",
                    ], 404);
                }
                // SIMPAN BUKTI BAYAR
                if ($request->file('proof_of_payment')) {
                    $data['proof_of_payment'] = $request->file('proof_of_payment')->store('assets/trx-product', 'public');
                }
            }


            TrxProduct::create($data);
            DB::commit();
            return response()->json([
                "status" => "success",
                "message" => "Transaksi Produk berhasil dibuat dan akan segera di proses oleh admin"
            ]);
        } catch (\Throwable $err) {
            DB::rollBack();
            if ($request->file("proof_of_payment")) {
                $uploadedImg = "public/assets/trx-product/" . $request->file("proof_of_payment")->hashName();
                if (Storage::exists($uploadedImg)) {
                    Storage::delete($uploadedImg);
                }
            }
            return response()->json([
                "status" => "error",
                "message" => $err->getMessage(),
            ], 500);
        }
    }
}
