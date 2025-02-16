<?php

namespace App\Http\Controllers\Dashboard;

use App\Exports\TrxDebtExport;
use App\Http\Controllers\Controller;
use App\Models\Bank;
use App\Models\TrxDebt;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class TrxDebtController extends Controller
{
    public function index()
    {
        $title = "Transaksi Piutang";
        $user = Auth::user();
        $pageUrl = "";
        $banks = [];
        $reseller = [];
        if ($user->role == "ADMIN") {
            $pageUrl = "pages.dashboard.admin.trx-debt";
            $reseller = User::where("role", "RESELLER")->select("id", "name", "code")->orderBy("name", "asc")->get();
        } else {
            $pageUrl = "pages.dashboard.reseller.trx-debt";
            $banks = Bank::all();
        }

        return view($pageUrl, compact("title", "reseller", "banks"));
    }

    public function export(Request $request)
    {
        return Excel::download(new TrxDebtExport($request), 'Transaksi Pihutang.xlsx');
    }

    // HANDLER API
    public function dataTable(Request $request)
    {
        try {
            $query = TrxDebt::with([
                "TrxProduct" => function ($query) {
                    $query->select("id", "code");
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
                $query->where('code', 'like', '%' . $searchValue . '%');
            }

            // filter reseller dari dashboard admin
            if ($request->query('user_id') && $request->query('user_id') != '') {
                $query->where('user_id', strtoupper($request->query('user_id')));
            }

            // filter type
            if ($request->query('type') && $request->query('type') != '') {
                $query->where('type', strtoupper($request->query('type')));
            }

            // filter status
            if ($request->query('status') && $request->query('status') != '') {
                $query->where('status', strtoupper($request->query('status')));
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
                    $action_success = $item->status == "PENDING" || $item->status == "PROCESS" ? "<a class='dropdown-item' onclick='return changeStatus(\"{$item->id}\", \"SUCCESS\");' href='javascript:void(0);' title='Success'>Success</a>" : "";
                    $action_reject = $item->status == "PENDING" || $item->status == "PROCESS" ? "<a class='dropdown-item' onclick='return changeStatus(\"{$item->id}\", \"REJECT\");' href='javascript:void(0);' title='Reject'>Reject</a>" : "";
                    $action_reason = $item->type == "P" && $item->status == "REJECT" ? "<a class='dropdown-item' onclick='return getData(\"{$item->id}\", \"SHOW-REASON-REJECT\");' href='javascript:void(0);' title='Alasan Ditolak'>Alasan Ditolak</a>" : "";
                    $action_show_proof_of_payment = $item->type == "P" ? "<a class='dropdown-item' onclick='return getData(\"{$item->id}\", \"SHOW-PROOF-PAYMENT\");' href='javascript:void(0);' title='Bukti Refund'>Bukti Transfer</a>" : "";

                    $action = " <div class='dropdown-primary dropdown open'>
                                <button class='btn btn-sm btn-primary dropdown-toggle waves-effect waves-light' id='dropdown-{$item->id}' data-toggle='dropdown' aria-haspopup='true' aria-expanded='true'>
                                    Aksi
                                </button>
                                <div class='dropdown-menu' aria-labelledby='dropdown-{$item->id}' data-dropdown-out='fadeOut'>
                                    " . $action_success . "
                                    " . $action_reject . "
                                    " . $action_reason . "
                                    " . $action_show_proof_of_payment . "
                                </div>
                            </div>";

                    if ($item->status == "CANCEL") {
                        $action = "";
                    }

                    $reseller = "<small> 
                                <strong>Nama</strong> :" . ($item->User ? $item->User->name : 'Reseller Deleted') .  "
                                <br>
                                <strong>Code</strong> :" . ($item->User ? $item->User->code : 'Reseller Deleted') . "
                                <br>
                            </small>";

                    if ($item->status == "CANCEL" || $item["type"] == "D") {
                        $action = "";
                    }
                    $item["action"] = $action;
                    $item["reseller"] = $reseller;
                } else {
                    $action_cancel = $item->type == "P" && $item->status == "PENDING" ? "<a class='dropdown-item' onclick='return changeStatus(\"{$item->id}\", \"CANCEL\");' href='javascript:void(0);' title='Cancel'>Cancel</a>" : "";
                    $action_reason = $item->type == "P" && $item->status == "REJECT" ? "<a class='dropdown-item' onclick='return getData(\"{$item->id}\", \"SHOW-REASON-REJECT\");' href='javascript:void(0);' title='Alasan Ditolak'>Alasan Ditolak</a>" : "";
                    $action_show_proof_of_payment = $item->type == "P" ? "<a class='dropdown-item' onclick='return getData(\"{$item->id}\", \"SHOW-PROOF-PAYMENT\");' href='javascript:void(0);' title='Bukti Refund'>Bukti Transfer</a>" : "";
                    $action = " <div class='dropdown-primary dropdown open'>
                                    <button class='btn btn-sm btn-primary dropdown-toggle waves-effect waves-light' id='dropdown-{$item->id}' data-toggle='dropdown' aria-haspopup='true' aria-expanded='true'>
                                        Aksi
                                    </button>
                                    <div class='dropdown-menu' aria-labelledby='dropdown-{$item->id}' data-dropdown-out='fadeOut'>
                                        " . $action_cancel . "
                                        " . $action_reason . "
                                        " . $action_show_proof_of_payment . "
                                    </div>
                                </div>";

                    if ($item->status == "CANCEL" || $item["type"] == "D") {
                        $action = "";
                    }
                    $item["action"] = $action;
                }

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
                $item["trx_ref"] = $item->TrxProduct ? $item->TrxProduct->code : "";
                $item['created'] = Carbon::parse($item->created_at)->addHours(7)->format('Y-m-d H:i:s');
                $item['updated'] = Carbon::parse($item->updated_at)->addHours(7)->format('Y-m-d H:i:s');
                if ($item['created'] == $item['updated']) {
                    $item['updated'] = '';
                }
                $item["bank"] = "";
                if ($item->Bank && $item->type == "P") {
                    $item["bank"] = "<strong>" . $item->Bank->title . " (" . $item->Bank->account . ")</strong>";
                }

                $item["debt_type"] = $item["type"];
                $item["type"] = $item["type"] == "D" ? "<span class='badge badge-danger'>Hutang</span>" : "<span class='badge badge-success'>Bayar</span>";

                unset($item['User']);
                unset($item['Product']);
                unset($item['Bank']);
                return $item;
            });

            $queryTotal = TrxDebt::whereBetween('created_at', [$tglAwal, $tglAkhir]);
            if ($user->role == "RESELLER") {
                $queryTotal->where('user_id', $user->id);
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

    public function getDetail($id)
    {
        try {
            $data = TrxDebt::with([
                "Bank" => function ($query) {
                    $query->select("id", "title", "account");
                }
            ])->where('id', $id)->first();

            if (!$data) {
                return response()->json([
                    "status" => "error",
                    "message" => "Data tidak ditemukan",
                ], 404);
            }

            $data["proof_of_payment"] = $data->proof_of_payment ? Storage::url($data->proof_of_payment) : null;
            $data["proof_of_return"] = $data->proof_of_return ? Storage::url($data->proof_of_return) : null;
            $data["amount"] = ' Rp. ' . number_format($data->amount, 0, ',', '.');
            $data["first_debt"] = ' Rp. ' . number_format($data->first_debt, 0, ',', '.');
            $data["last_debt"] = ' Rp. ' . number_format($data->last_debt, 0, ',', '.');
            $data["bank"] = "";
            if ($data->Bank && $data->type == "P") {
                $data["bank"] = "<strong>" . $data->Bank->title . " (" . $data->Bank->account . ")</strong>";
            }
            unset($data["Bank"]);

            return response()->json([
                "status" => "success",
                "data" => $data
            ]);
        } catch (\Throwable $err) {
            return response()->json([
                "status" => "error",
                "message" => $err->getMessage(),
            ], 500);
        }
    }

    public function create(Request $request)
    {
        try {
            DB::beginTransaction();
            $data = $request->all();
            $user = auth()->user();
            $rules = [
                "amount" => "required|integer|min:0",
                "type" => "required|string|in:D,P" // dept or pay
            ];

            $messages = [
                "amount.required" => "Nominal harus diisi",
                "amount.integer" => "Nominal tidak sesuai",
                "amount.min" => "Nominal tidak boleh minus",
                "type.required" => "Tipe harus diisi",
                "type.in" => "Tipe tidak sesuai"
            ];

            if ($user->role == "RESELLER") {
                $rules["bank_id"] = "required|integer";
                $rules["proof_of_payment"] = "required|image|max:2048|mimes:giv,svg,jpeg,png,jpg";

                $messages["bank_id.requied"] = "Bank Pembayaran harus dipilih";
                $messages["bank_id.integer"] = "Bank Pembayaran tidak valid";
                $messages["proof_of_payment.required"] = "Bukti pembayaran harus diisi";
                $messages["proof_of_payment.image"] = "Bukti pembayaran tidak valid";
                $messages["proof_of_payment.max"] = "Bukti pembayaran maximal 2MB";
                $messages["proof_of_payment.mimes"] = "Format Bukti pembayaran harus giv/svg/jpeg/png/jpg";
            } else {
                $rules["user_id"] = "required|integer";

                $messages["user_id.requied"] = "Reseller harus dipilih";
                $messages["user_id.integer"] = "Reseller tidak valid";
            }

            $validator = Validator::make($data, $rules, $messages);
            if ($validator->fails()) {
                return response()->json([
                    "status" => "error",
                    "message" => $validator->errors()->first(),
                ], 400);
            }

            // CEK DATA BANK
            if ($user->role == "RESELLER") {
                $bank = Bank::where("id", $data["bank_id"])->first();
                if (!$bank) {
                    return response()->json([
                        "status" => "error",
                        "message" => "Data Bank tidak ditemukan"
                    ], 404);
                }
            }

            // cek data reseeler dulu
            $reseller = User::find(auth()->user()->id); // by default ambil dari auth jika yg login adalah user
            if ($user->role == "ADMIN") {
                $reseller = User::find($data["user_id"]);
                if (!$reseller) {
                    return response()->json([
                        "status" => "error",
                        "message" => "Reseller tidak ditemukan",
                    ], 404);
                }
            }

            $resellerFirstDebt = $reseller->total_debt;
            $resellerLastDebt = $reseller->total_debt;

            // JIKA TIPE NYA RESELLER MELAKUKAN HUTANG BARU DAN DATA DIBUAT OLEH ADMIN
            if ($data["type"]  == "D" && $user->role == "ADMIN") {
                // / CEK LIMIT DEBT
                $limitDebt = $reseller->debt_limit;
                $totalDebt = $reseller->total_debt;
                $allowedDebt = $limitDebt - $totalDebt; // sisa pihutang yg masih bisa dipakai

                // jika jumlah pesanan melebihi limit pihutang
                if ($limitDebt < $data["amount"]) {
                    return response()->json([
                        "status" => "error",
                        "message" => "Limit Pihutang reseller tidak cukup",
                    ], 400);
                }

                // jika sisa pihutang tidak cukup
                if ($allowedDebt < $data["amount"]) {
                    return response()->json([
                        "status" => "error",
                        "message" => "Total Pihutang reseller sudah terlalu banyak untuk melanjutkan transaksi ini",
                    ], 400);
                }

                // UPDATE TOTAL_DEBT RESELLER
                $updateReseller["total_debt"] = $totalDebt + $data["amount"];
                $reseller->update($updateReseller);
                $resellerLastDebt = $reseller->total_debt; // update main value variabel

                // SIMPAN DATA PIUTANG
                TrxDebt::create([
                    "code" => "TRXDB" . strtoupper(Str::random(5)),
                    "user_id" => $reseller->id,
                    "amount" => $data["amount"],
                    "status" => "SUCCESS",
                    "type" => "D",
                    "first_debt" => $resellerFirstDebt,
                    "last_debt" => $resellerLastDebt,
                    "remark" => $data["remark"] ?? "Piutang dibuat oleh admin"
                ]);
            } else if ($data["type"] == "P") {
                $resellerLastDebt = $resellerLastDebt - $data["amount"];
                $dataPiutang = [
                    "code" => "TRXDB" . strtoupper(Str::random(5)),
                    "user_id" => $reseller->id,
                    "amount" => $data["amount"],
                    "type" => "P",
                    "first_debt" => $resellerFirstDebt,
                    "last_debt" => $resellerLastDebt,
                    "remark" => $data["remark"] ?? "Pembayaran piutang oleh " . $user->role
                ];

                // jika yg bayar langsung dari admin. mana transaksi langsung sukses dan update pihutang reseller
                if ($user->role == "ADMIN") {
                    $dataPiutang["status"] = "SUCCESS";
                    $updateReseller["total_debt"] = $resellerFirstDebt - $data["amount"];
                    $reseller->update($updateReseller);
                } else {
                    // jika request pembayaran dari reseller. simpan data bank dan bukti bayar
                    $bank = Bank::find($data["bank_id"]);
                    if (!$bank) {
                        return response()->json([
                            "status" => "error",
                            "message" => "Data Bank Pembayaran tidak ditemukan",
                        ], 404);
                    }
                    $dataPiutang["status"] = "PENDING";
                    $dataPiutang["bank_id"] = $data["bank_id"];
                    // SIMPAN BUKTI BAYAR
                    if ($request->file('proof_of_payment')) {
                        $dataPiutang['proof_of_payment'] = $request->file('proof_of_payment')->store('assets/trx-debt', 'public');
                    }
                }

                TrxDebt::create($dataPiutang);
            }

            DB::commit();
            return response()->json([
                "status" => "success",
                "message" => "Transaksi Pihutang berhasil ditambahkan"
            ]);
        } catch (\Throwable $err) {
            if ($request->file("proof_of_payment")) {
                $uploadedImg = "public/assets/trx-debt/" . $request->file("proof_of_payment")->hashName();
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

    // HANYA DIGUNAKAN UNTUK MENGUBAH STATUS PEMBAYARAN PIUTANG DARI RESELLER
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
                "proof_of_return.image" => "Gambar yang di upload tidak valid",
                "proof_of_return.max" => "Ukuran gambar maximal 2MB",
                "proof_of_return.mimes" => "Format gambar harus giv/svg/jpeg/png/jpg",
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
            $accessAdmin = ["SUCCESS", "REJECT"];
            if (in_array($data["status"], $accessAdmin) && $user->role != "ADMIN") {
                return response()->json([
                    "status" => "error",
                    "message" => "Opps. anda tidak memiliki akses"
                ], 403);
            }

            $dataTrx = TrxDebt::find($data["id"]);
            if (!$dataTrx) {
                return response()->json([
                    "status" => "error",
                    "message" => "Data Piutang tidak ditemukan !"
                ], 404);
            }

            // VALIDASI TRX . JIKA SUDAH REJECT / CANCEL TIDAK BOLEH DIUBAH LAGI STATUSNYA
            if (in_array($dataTrx->status, ['REJECT', 'CANCEL'])) {
                return response()->json([
                    "status" => "error",
                    "message" => "Status Piutang sudah tidak bisa diubah"
                ], 400);
            }

            $reseller = User::find($dataTrx->user_id);
            if (!$reseller) {
                return response()->json([
                    "status" => "error",
                    "message" => "Data Reseller atas transaksi ini tidak ditemukan"
                ], 404);
            }

            // JIKA ADMIN MENYUKSESKAN REQUEST BAYAR PIUTANG OLEH RESELLER . KURANGI TOTAL PIUTANG RESELLER
            if ($data["status"] == "SUCCESS" && $dataTrx["type"] == "P") {
                $updateReseller = ["total_debt" => $reseller->total_debt - $dataTrx->amount];
                $reseller->update($updateReseller);
            }

            // SIMPAN BUKTI REFUND JIKA ADA
            unset($data["proof_of_return"]);
            if ($request->file("proof_of_return")) {
                $data["proof_of_return"] = $request->file("proof_of_return")->store("assets/trx-debt", "public");
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
                $uploadedImg = "public/assets/trx-debt/" . $request->file("proof_of_return")->hashName();
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
