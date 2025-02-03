<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Bank;
use App\Models\Mutation;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\TrxDebt;
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
                    $query->select("id", "title", "code", "product_category_id")
                        ->with("ProductCategory:id,title");
                },
                "User" => function ($query) {
                    $query->select("id", "code", "name");
                },
                // "Bank" => function ($query) {
                //     $query->select("id", "title", "account");
                // }
            ]);

            // filter by reseller ID
            $user = auth()->user();
            if ($user->role == "RESELLER") {
                $query->where('user_id', $user->id);
            }

            // filter code trx
            if ($request->query("search")) {
                $searchValue = $request->query("search")['value'];
                $query->where('code', 'like', '%' . $searchValue . '%');
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
            $tglAwal = $request->query('tgl_awal');
            $tglAkhir = $request->query('tgl_akhir');

            if (!$tglAwal) {
                $tglAwal = Carbon::now('UTC')->startOfMonth()->subHour(7)->toDateTimeString(); // dikurangi 7 jam mengikuti waktu utc
            }

            if (!$tglAkhir) {
                $tglAkhir = Carbon::now('UTC')->endOfMonth()->subHour(7)->toDateTimeString(); // dikurangi 7 jam mengikuti waktu utc
            }

            if ($request->query('tgl_awal') && $request->query('tgl_akhir')) {
                $tglAwal = Carbon::createFromFormat('d/m/Y', $request->query('tgl_awal'), 'UTC')->startOfDay()->subHour(7)->toDateTimeString(); // dikurangi 7 jam mengikuti waktu utc
                $tglAkhir = Carbon::createFromFormat('d/m/Y', $request->query('tgl_akhir'), 'UTC')->endOfDay()->subHour(7)->toDateTimeString(); // dikurangi 7 jam mengikuti waktu utc
            }

            $query->whereBetween('created_at', [$tglAwal, $tglAkhir]);
            $recordsFiltered = $query->count();
            if ($request->query('start')) {
                $query->skip($request->query('start'));
            }
            if ($request->query('length')) {
                $query->limit($request->query('length'));
            }
            $data = $query->orderBy('id', 'desc')
                ->get();

            $output = $data->map(function ($item) use ($user) {
                if ($user->role == "ADMIN") {
                    $action_process = $item->status == "PENDING" ? "<a class='dropdown-item' onclick='return changeStatus(\"{$item->id}\", \"PROCESS\");' href='javascript:void(0);' title='In Process'>In Process</a>" : "";
                    $action_success = $item->status == "PENDING" || $item->status == "PROCESS" ? "<a class='dropdown-item' onclick='return changeStatus(\"{$item->id}\", \"SUCCESS\");' href='javascript:void(0);' title='Success'>Success</a>" : "";
                    $action_reject = $item->status == "PENDING" || $item->status == "PROCESS" ? "<a class='dropdown-item' onclick='return changeStatus(\"{$item->id}\", \"REJECT\", \"{$item->payment_type}\");' href='javascript:void(0);' title='Reject'>Reject</a>" : "";
                    $action_reason =  $item->status == "REJECT" && $item->payment_type == "DEBT" ? "<a class='dropdown-item' onclick='return getData(\"{$item->id}\", \"SHOW-REASON-REJECT\");' href='javascript:void(0);' title='Alasan Ditolak'>Alasan Ditolak</a>" : "";
                    $action_show_proof_of_payment = $item->payment_type == "TRANSFER" ? "<a class='dropdown-item' onclick='return getData(\"{$item->id}\", \"SHOW-PROOF-PAYMENT\");' href='javascript:void(0);' title='Bukti Refund'>Bukti Transfer</a>" : "";
                    $action_show_proof_of_return = $item->status == "REJECT" && $item->payment_type == "TRANSFER" ? "<a class='dropdown-item' onclick='return getData(\"{$item->id}\", \"SHOW-PROOF-RETURN\");' href='javascript:void(0);' title='Bukti Refund'>Bukti Refund</a>" : "";

                    $action = " <div class='dropdown-primary dropdown open'>
                                <button class='btn btn-sm btn-primary dropdown-toggle waves-effect waves-light' id='dropdown-{$item->id}' data-toggle='dropdown' aria-haspopup='true' aria-expanded='true'>
                                    Aksi
                                </button>
                                <div class='dropdown-menu' aria-labelledby='dropdown-{$item->id}' data-dropdown-out='fadeOut'>
                                    " . $action_process . "
                                    " . $action_success . "
                                    " . $action_reject . "
                                    " . $action_reason . "
                                    " . $action_show_proof_of_payment . "
                                    " . $action_show_proof_of_return . "
                                </div>
                            </div>";

                    if ($item->status == "CANCEL") {
                        $action = "";
                    }

                    $item["action"] = $action;
                } else {
                    $action_cancel = $item->status == "PENDING" ? "<a class='dropdown-item' onclick='return changeStatus(\"{$item->id}\", \"CANCEL\");' href='javascript:void(0);' title='Cancel'>Cancel</a>" : "";
                    $action_reason =  $item->status == "REJECT" && $item->payment_type == "DEBT" ? "<a class='dropdown-item' onclick='return getData(\"{$item->id}\", \"SHOW-REASON-REJECT\");' href='javascript:void(0);' title='Alasan Ditolak'>Alasan Ditolak</a>" : "";
                    $action_show_proof_of_payment = $item->payment_type == "TRANSFER" ? "<a class='dropdown-item' onclick='return getData(\"{$item->id}\", \"SHOW-PROOF-PAYMENT\");' href='javascript:void(0);' title='Bukti Refund'>Bukti Transfer</a>" : "";
                    $action_show_proof_of_return = $item->status == "REJECT" && $item->payment_type == "TRANSFER" ? "<a class='dropdown-item' onclick='return getData(\"{$item->id}\", \"SHOW-PROOF-RETURN\");' href='javascript:void(0);' title='Bukti Refund'>Bukti Refund</a>" : "";
                    $action = " <div class='dropdown-primary dropdown open'>
                                <button class='btn btn-sm btn-primary dropdown-toggle waves-effect waves-light' id='dropdown-{$item->id}' data-toggle='dropdown' aria-haspopup='true' aria-expanded='true'>
                                    Aksi
                                </button>
                                <div class='dropdown-menu' aria-labelledby='dropdown-{$item->id}' data-dropdown-out='fadeOut'>
                                    " . $action_cancel . "
                                    " . $action_reason . "
                                    " . $action_show_proof_of_payment . "
                                    " . $action_show_proof_of_return . "
                                </div>
                            </div>";

                    if ($item->status == "CANCEL") {
                        $action = "";
                    }
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
                                <strong>Nama</strong> :" . ($item->User ? $item->User->name : 'Reseller Deleted') .  "
                                <br>
                                <strong>Code</strong> :" . ($item->User ? $item->User->code : 'Reseller Deleted') . "
                                <br>
                            </small>";

                $classStatus = "";
                switch ($item["status"]) {
                    case "PENDING":
                        $classStatus = "badge-info";
                        break;
                    case "PROCESS":
                        $classStatus = "badge-primary";
                        break;
                    case "SUCCESS":
                        $classStatus = "badge-success";
                        break;
                    case "REJECT":
                        $classStatus = "badge-danger";
                        break;
                    case "CANCEL":
                        $classStatus = "badge-warning";
                }

                $item["status"] = "<span class='badge " . $classStatus . "'>" . $item["status"] . "</span>";
                $item['product'] = $product;
                $item['reseller'] = $reseller;
                $item['payment_type'] = $item['payment_type'] == "TRANSFER" ? "TRANSFER BANK" : "PIHUTANG";
                $item['created'] = Carbon::parse($item->created_at)->addHours(7)->format('Y-m-d H:i:s');
                $item['updated'] = Carbon::parse($item->updated_at)->addHours(7)->format('Y-m-d H:i:s');
                if ($item['created'] == $item['updated']) {
                    $item['updated'] = '';
                }

                unset($item['Product']);
                unset($item['User']);
                unset($item['Bank']);
                return $item;
            });

            $queryTotal = TrxProduct::query();
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
            $userFirstDebt = $user->total_debt;
            $userLastDebt = $user->total_debt;
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
                $userLastDebt = $user->total_debt; // update main value variabel
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

            // UPDATE STOK PRODUK
            $updateStockProduct = [
                "stock" => $product->stock - $data["qty"]
            ];
            $product->update($updateStockProduct);

            $trxProduct = TrxProduct::create($data);
            // CREATE HISTORY PIHUTANG JIKA METODE BAYAR NYA HUTANG
            if ($data["payment_type"] == "DEBT") {
                TrxDebt::create([
                    "user_id" => $user->id,
                    "trx_product_id" => $trxProduct->id,
                    "amount" => $totalAmount,
                    "status" => "SUCCESS",
                    "first_debt" => $userFirstDebt,
                    "last_debt" => $userLastDebt,
                    "remark" => "Pembelian " . $data["qty"] . " Produk " . $product->title . " dengan pembayaran piutang"
                ]);
            }

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

    public function getDetail($id)
    {
        try {
            $trxProduct = TrxProduct::with([
                "Bank" => function ($query) {
                    $query->select("id", "title", "account");
                }
            ])->where('id', $id)->first();

            if (!$trxProduct) {
                return response()->json([
                    "status" => "error",
                    "message" => "Data tidak ditemukan",
                ], 404);
            }

            $output = [
                "payment_type" => $trxProduct->payment_type,
                "bank_target" => $trxProduct->Bank ? $trxProduct->Bank->title . ' (' . $trxProduct->Bank->account .')'  : "",
                "proof_of_payment" => $trxProduct->proof_of_payment ? Storage::url($trxProduct->proof_of_payment) : null,
                "proof_of_return" => $trxProduct->proof_of_return ? Storage::url($trxProduct->proof_of_return) : null,
                "reason" => $trxProduct->remark
            ];

            return response()->json([
                "status" => "success",
                "data" => $output
            ]);
        } catch (\Throwable $err) {
            return response()->json([
                "status" => "error",
                "message" => $err->getMessage(),
            ], 500);
        }
    }

    // public function getDetail($id)
    // {
    //     try {
    //         $trxProduct = TrxProduct::with([
    //             "Product" => function ($query) {
    //                 $query->select("id", "title", "code", "product_category_id")
    //                     ->with("ProductCategory:id,title");
    //             },
    //             "User" => function ($query) {
    //                 $query->select("id", "code", "name");
    //             },
    //             "Bank" => function ($query) {
    //                 $query->select("id", "title", "account");
    //             }
    //         ])->where('id', $id)->first();

    //         if (!$trxProduct) {
    //             return response()->json([
    //                 "status" => "error",
    //                 "message" => "Data tidak ditemukan",
    //             ], 404);
    //         }

    //         $classStatus = "";
    //         switch ($trxProduct->status) {
    //             case "PENDING":
    //                 $classStatus = "badge-info";
    //                 break;
    //             case "PROCESS":
    //                 $classStatus = "badge-primary";
    //                 break;
    //             case "SUCCESS":
    //                 $classStatus = "badge-success";
    //                 break;
    //             case "REJECT":
    //                 $classStatus = "badge-danger";
    //                 break;
    //             case "CANCEL":
    //                 $classStatus = "badge-warning";
    //         }

    //         $output = [
    //             "code" => $trxProduct->code,
    //             "status" => "<span class='badge " . $classStatus . "'>" . $trxProduct->status . "</span>",
    //             "product" => $trxProduct->Product ? $trxProduct->Product->title : 'Product Deleted',
    //             "product_code" => $trxProduct->Product ? $trxProduct->Product->code : 'Product Deleted',
    //             "qty" => $trxProduct->qty,
    //             "amount" => "Rp. " . number_format($trxProduct->amount, 0, ',', '.'),
    //             "total_amount" => "Rp. " . number_format($trxProduct->total_amount, 0, ',', '.'),
    //             "commission" => "Rp. " . number_format($trxProduct->commission, 0, ',', '.'),
    //             "payment_type" => $trxProduct->payment_type,
    //             "bank_target" => $trxProduct->Bank ? $trxProduct->Bank->title : "",
    //             "created" => Carbon::parse($trxProduct->created_at)->addHours(7)->format('Y-m-d H:i:s'),
    //             "updated" => Carbon::parse($trxProduct->created_at)->addHours(7)->format('Y-m-d H:i:s'),
    //             "proof_of_payment" => $trxProduct->proof_of_payment ? Storage::url($trxProduct->proof_of_payment) : null,
    //             "proof_of_return" => $trxProduct->proof_of_return ? Storage::url($trxProduct->proof_of_return) : null,
    //         ];

    //         $user = auth()->user();
    //         if ($user->role == "ADMIN") {
    //             $additional = [
    //                 "reseller_name" => $trxProduct->User ? $trxProduct->User->name : 'Reseller Deleted',
    //                 "reseller_code" => $trxProduct->User ? $trxProduct->User->code : 'Reseller Deleted',
    //                 "profit" =>  "Rp. " . number_format($trxProduct->profit, 0, ',', '.'),
    //             ];

    //             $output = array_merge($output, $additional);
    //         }

    //         return response()->json([
    //             "status" => "success",
    //             "data" => $output
    //         ]);
    //     } catch (\Throwable $err) {
    //         return response()->json([
    //             "status" => "error",
    //             "message" => $err->getMessage(),
    //         ], 500);
    //     }
    // }
    public function changeStatus(Request $request)
    {
        try {
            DB::beginTransaction();
            $data = $request->all();
            $rules = [
                "id" => "required|integer",
                "status" => "required|string|in:PROCESS,SUCCESS,REJECT,CANCEL",
                "proof_of_return" => "nullable",
                "remark" => "nullable"
            ];

            if ($request->file('proof_of_return')) {
                $rules['proof_of_return'] .= '|image|max:2048|mimes:giv,svg,jpeg,png,jpg';
            }

            $messages = [
                "id.required" => "Data Transaksi harus dipilih",
                "id.integer" => "Type Transaksi tidak valid",
                "status.required" => "Status harus diisi",
                "status.in" => "Status tidak sesuai",
                "image.image" => "Gambar yang di upload tidak valid",
                "image.max" => "Ukuran gambar maximal 2MB",
                "image.mimes" => "Format gambar harus giv/svg/jpeg/png/jpg",
            ];

            $validator = Validator::make($data, $rules, $messages);
            if ($validator->fails()) {
                return response()->json([
                    "status" => "error",
                    "message" => $validator->errors()->first(),
                ], 400);
            }

            // VALIDASI HAK AKSES PERUBAHAN STATUS
            $user = auth()->user();
            $accessAdmin = ["PROCESS", "SUCCESS", "REJECT"];
            if (in_array($data["status"], $accessAdmin) && $user->role != "ADMIN") {
                return response()->json([
                    "status" => "error",
                    "message" => "Opps. anda tidak memiliki akses"
                ], 403);
            }

            $dataTrx = TrxProduct::find($data["id"]);
            if (!$dataTrx) {
                return response()->json([
                    "status" => "error",
                    "message" => "Data Transaksi tidak ditemukan !"
                ], 404);
            }

            // VALIDASI TRX . JIKA SUDAH REJECT / CANCEL TIDAK BOLEH DIUBAH LAGI STATUSNYA
            if (in_array($dataTrx->status, ['REJECT', 'CANCEL'])) {
                return response()->json([
                    "status" => "error",
                    "message" => "Status transaksi sudah tidak bisa diubah"
                ], 400);
            }

            $reseller = User::find($dataTrx->user_id);
            if (!$reseller) {
                return response()->json([
                    "status" => "error",
                    "message" => "Data Reseller atas transaksi ini tidak ditemukan"
                ], 404);
            }

            // JIKA SUCCESS LAKUKAN SHARE COMMISSION RESELLER
            if ($data["status"] == "SUCCESS") {
                // SIMPAN MUTASI KOMISI
                $dataMutasi = [
                    "amount" => $dataTrx->commission,
                    "type" => "C", // commission,
                    "first_commission" => $reseller->commission,
                    "last_commission" => $reseller->commission + $dataTrx->commission,
                    "trx_product_id" => $dataTrx->id,
                    "user_id" => $reseller->id,
                ];
                Mutation::create($dataMutasi);
                // UPDATE SALDO RESELLER
                $updateReseller = ["commission" => $reseller->commission + $dataTrx->commission];
                $reseller->update($updateReseller);
            }

            // JIKA REJECT/CANCEL DAN TIPE NYA PIHUTANG, UPDATE NOMINAL PIHUTANGNYA DAN UPDATE STOCK PRODUK
            if (in_array($data["status"], ["REJECT", "CANCEL"]) && $dataTrx->payment_type == "DEBT") {
                // PIHUTNG RESELLER
                $totalDebtBefore = $reseller->total_debt;
                $totalDebtAfter = $totalDebtBefore - $dataTrx->total_amount;
                $updateReseller = ["total_debt" => $totalDebtAfter];
                $reseller->update($updateReseller);


                // STOCK PRODUK
                $product = Product::find($dataTrx->product_id);
                $updatedStock = [
                    "stock" => $product->stock + $dataTrx->qty
                ];
                $product->update($updatedStock);

                // HAPUS HISTORY PIUTANG
                TrxDebt::where("trx_product_id", $dataTrx->id)->delete();
            }

            // SIMPAN BUKTI REFUND JIKA ADA
            unset($data["proof_of_return"]);
            if ($request->file("proof_of_return")) {
                $data["proof_of_return"] = $request->file("proof_of_return")->store("assets/trx-product", "public");
            }

            $dataTrx->update($data);
            DB::commit();
            return response()->json([
                "status" => "success",
                "message" => "Status Transaksi berhasil diperbarui"
            ]);
        } catch (\Throwable $err) {
            DB::rollBack();
            if ($request->file("proof_of_return")) {
                $uploadedImg = "public/assets/trx-product/" . $request->file("proof_of_return")->hashName();
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
