<?php

namespace App\Http\Controllers\Dashboard;

use App\Exports\TrxCommissionExport;
use App\Http\Controllers\Controller;
use App\Models\Mutation;
use App\Models\TrxCommission;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class TrxCommissionController extends Controller
{
    public function index()
    {
        $title = "Transaksi Komisi";
        $user = Auth::user();
        $pageUrl = $user->role == "ADMIN" ? "pages.dashboard.admin.trx-commission" : "pages.dashboard.reseller.trx-commission";

        return view($pageUrl, compact("title"));
    }

    public function requestWithdraw()
    {
        $user = User::where("id", auth()->user()->id)->first();
        $balance = $user->commission;
        $title = "Request Withdraw";
        $bank_type = $user->bank_type ?? "";
        $bank_account = $user->bank_account ?? "";

        return view("pages.dashboard.reseller.request-wd", compact("title", "balance", "bank_type", "bank_account"));
    }

    public function export(Request $request)
    {
        return Excel::download(new TrxCommissionExport($request), 'Transaksi Withdraw Komisi.xlsx');
    }

    // API
    public function dataTable(Request $request)
    {
        try {
            $query = TrxCommission::with([
                "User" => function ($query) {
                    $query->select("id", "code", "name");
                }
            ]);

            // FILTER BY RESELLER ID
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
            $data = $query->orderBy('id', 'desc')->get();
            $output = $data->map(function ($item) use ($user) {
                if ($user->role == "ADMIN") {
                    $action_process = $item->status == "PENDING" ? "<a class='dropdown-item' onclick='return changeStatus(\"{$item->id}\", \"PROCESS\");' href='javascript:void(0);' title='In Process'>In Process</a>" : "";
                    $action_success = $item->status == "PENDING" || $item->status == "PROCESS" ? "<a class='dropdown-item' onclick='return changeStatus(\"{$item->id}\", \"SUCCESS\");' href='javascript:void(0);' title='Success'>Success</a>" : "";
                    $action_reject = $item->status == "PENDING" || $item->status == "PROCESS" ? "<a class='dropdown-item' onclick='return changeStatus(\"{$item->id}\", \"REJECT\");' href='javascript:void(0);' title='Reject'>Reject</a>" : "";
                    $action_reason =  $item->status == "REJECT" ? "<a class='dropdown-item' onclick='return getData(\"{$item->id}\", \"SHOW-REASON-REJECT\");' href='javascript:void(0);' title='Alasan Ditolak'>Alasan Ditolak</a>" : "";
                    $action_detail = $item->status == "PENDING" || $item->status == "PROCESS" ? "<a class='dropdown-item' onclick='return getData(\"{$item->id}\", \"DETAIL\");' href='javascript:void(0);' title='Detail'>Detail</a>" : "";
                    $action_show_proof_of_payment = $item->status == "SUCCESS" ? "<a class='dropdown-item' onclick='return getData(\"{$item->id}\", \"SHOW-PROOF-PAYMENT\");' href='javascript:void(0);' title='Bukti Refund'>Bukti Transfer</a>" : "";

                    $action = " <div class='dropdown-primary dropdown open'>
                                <button class='btn btn-sm btn-primary dropdown-toggle waves-effect waves-light' id='dropdown-{$item->id}' data-toggle='dropdown' aria-haspopup='true' aria-expanded='true'>
                                    Aksi
                                </button>
                                <div class='dropdown-menu' aria-labelledby='dropdown-{$item->id}' data-dropdown-out='fadeOut'>
                                    " . $action_detail . "
                                    " . $action_process . "
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

                    $item["action"] = $action;
                    $item["reseller"] = $reseller;
                } else {
                    $action_cancel = $item->status == "PENDING" ? "<a class='dropdown-item' onclick='return changeStatus(\"{$item->id}\", \"CANCEL\");' href='javascript:void(0);' title='Cancel'>Cancel</a>" : "";
                    $action_reason =  $item->status == "REJECT" ? "<a class='dropdown-item' onclick='return getData(\"{$item->id}\", \"SHOW-REASON-REJECT\");' href='javascript:void(0);' title='Alasan Ditolak'>Alasan Ditolak</a>" : "";
                    $action_detail = $item->status == "PENDING" || $item->status == "PROCESS" ? "<a class='dropdown-item' onclick='return getData(\"{$item->id}\", \"DETAIL\");' href='javascript:void(0);' title='Detail'>Detail</a>" : "";
                    $action_show_proof_of_payment = $item->status == "SUCCESS" ? "<a class='dropdown-item' onclick='return getData(\"{$item->id}\", \"SHOW-PROOF-PAYMENT\");' href='javascript:void(0);' title='Bukti Refund'>Bukti Transfer</a>" : "";
                    $action = " <div class='dropdown-primary dropdown open'>
                                    <button class='btn btn-sm btn-primary dropdown-toggle waves-effect waves-light' id='dropdown-{$item->id}' data-toggle='dropdown' aria-haspopup='true' aria-expanded='true'>
                                        Aksi
                                    </button>
                                    <div class='dropdown-menu' aria-labelledby='dropdown-{$item->id}' data-dropdown-out='fadeOut'>
                                        " . $action_detail . "
                                        " . $action_cancel . "
                                        " . $action_reason . "
                                        " . $action_show_proof_of_payment . "
                                    </div>
                                </div>";

                    if ($item->status == "CANCEL") {
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
                $target = "<small> 
                                <strong>Bank</strong> :" . $item->bank_name .  "
                                <br>
                                <strong>Rekening</strong> :" . $item->bank_account . "
                                <br>
                            </small>";

                $item["status"] = "<span class='badge " . $classStatus . "'>" . $item["status"] . "</span>";
                $item["target"] = $target;
                $item['created'] = Carbon::parse($item->created_at)->addHours(7)->format('Y-m-d H:i:s');
                $item['updated'] = Carbon::parse($item->updated_at)->addHours(7)->format('Y-m-d H:i:s');
                if ($item['created'] == $item['updated']) {
                    $item['updated'] = '';
                }
                unset($item['User']);
                return $item;
            });

            $queryTotal = TrxCommission::whereBetween('created_at', [$tglAwal, $tglAkhir]);
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

    public function create(Request $request)
    {
        try {
            DB::beginTransaction();
            $data = $request->all();
            $rules = [
                "amount" => "integer|min:100000|max:1000000|required",
                "bank_name" => "string|required",
                "bank_account" => "string|required"
            ];

            $messages = [
                "amount.integer" => "Nominal Penarikan tidak valid",
                "amount.min" => "Nominal Penarikan minimal Rp.100.000",
                "amount.max" => "Nominal Penarikan maksimal Rp.1.000.000",
                "amount.requred" => "Nominal Penarikan harus diisi",
                "bank_name.required" => "Nama Bank harus diisi",
                "bank_account" => "No Rekening harus diisi"
            ];

            $validator = Validator::make($data, $rules, $messages);
            if ($validator->fails()) {
                return response()->json([
                    "status" => "error",
                    "message" => $validator->errors()->first(),
                ], 400);
            }

            $user = User::where("id", auth()->user()->id)->first();
            // CEK KOMISI RESELLER
            $admin = 6500;
            $totalAmount = $data["amount"] + $admin;
            if ($user->commission < $totalAmount) {
                return response()->json([
                    "status" => "error",
                    "message" => "Mohon maaf saldo anda tidak mencukupi"
                ], 400);
            }

            // UPDATE KOMISI RESELLER
            $firstCommission = $user->commission;
            $updatedCommission = $firstCommission - $totalAmount;
            $dataToUpdate = [
                "commission" => $updatedCommission
            ];
            $user->update($dataToUpdate);

            // SIMPAN TRANSAKSI KOMISI
            $trx = TrxCommission::create([
                "code" => "TRXCM" . strtoupper(Str::random(5)),
                "amount" => $data["amount"],
                "admin" => $admin,
                "total_amount" => $totalAmount,
                "bank_name" => $data["bank_name"],
                "bank_account" => $data["bank_account"],
                "user_id" => $user->id,
                "status" => "PENDING",
                "remark" => $data["notes"]
            ]);

            // SIMPAN MUTASI WITHDRAW
            Mutation::create([
                "code" => "MUTAT" . strtoupper(Str::random(5)),
                "amount" => $totalAmount,
                "type" => "W", // withdraw
                "first_commission" => $firstCommission,
                "last_commission" => $updatedCommission,
                "trx_commission_id" => $trx->id,
                "user_id" => $user->id
            ]);

            DB::commit();
            return response()->json([
                "status" => "success",
                "message" => "Penarikan berhasil diajukan, silahkan tunggu admin untuk memproses"
            ]);
        } catch (\Throwable $err) {
            DB::rollBack();
            return response()->json([
                "status" => "error",
                "message" => $err->getMessage()
            ], 500);
        }
    }

    public function getDetail($id)
    {
        try {
            $data = TrxCommission::where('id', $id)->first();

            if (!$data) {
                return response()->json([
                    "status" => "error",
                    "message" => "Data tidak ditemukan",
                ], 404);
            }

            $data["proof_of_payment"] = $data->proof_of_payment ? Storage::url($data->proof_of_payment) : null;
            $data["amount"] = ' Rp. ' . number_format($data->amount, 0, ',', '.');
            $data["admin"] = ' Rp. ' . number_format($data->admin, 0, ',', '.');
            $data["total_amount"] = ' Rp. ' . number_format($data->total_amount, 0, ',', '.');

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

    public function changeStatus(Request $request)
    {
        try {
            DB::beginTransaction();
            $data = $request->all();
            $rules = [
                "id" => "required|integer",
                "status" => "required|string|in:PROCESS,SUCCESS,REJECT,CANCEL",
                "proof_of_payment" => "nullable",
                "remark" => "nullable"
            ];

            if ($request->file('proof_of_payment')) {
                $rules['proof_of_payment'] .= '|image|max:2048|mimes:giv,svg,jpeg,png,jpg';
            }

            $messages = [
                "id.required" => "Data Penarikan harus dipilih",
                "id.integer" => "Type Penarikan tidak valid",
                "status.required" => "Status harus diisi",
                "status.in" => "Status tidak sesuai",
                "proof_of_payment.image" => "Gambar yang di upload tidak valid",
                "proof_of_payment.max" => "Ukuran gambar maximal 2MB",
                "proof_of_payment.mimes" => "Format gambar harus giv/svg/jpeg/png/jpg",
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

            $dataTrx = TrxCommission::find($data["id"]);
            if (!$dataTrx) {
                return response()->json([
                    "status" => "error",
                    "message" => "Data Penarikan tidak ditemukan !"
                ], 404);
            }

            // VALIDASI TRX . JIKA SUDAH REJECT / CANCEL TIDAK BOLEH DIUBAH LAGI STATUSNYA
            if (in_array($dataTrx->status, ['REJECT', 'CANCEL'])) {
                return response()->json([
                    "status" => "error",
                    "message" => "Status Penarikan sudah tidak bisa diubah"
                ], 400);
            }

            $reseller = User::find($dataTrx->user_id);
            if (!$reseller) {
                return response()->json([
                    "status" => "error",
                    "message" => "Data Reseller atas transaksi ini tidak ditemukan"
                ], 404);
            }

            // JIKA REJECT / CANCEL . REFUND SALDO KOMISI RESELLER DAN BUAT DATA MUTASI REFUND
            if (in_array($data["status"], ["REJECT", "CANCEL"])) {
                $firstCommission = $reseller->commission;
                $lastCommission = $firstCommission + $dataTrx->total_amount;

                // SIMPAN MUTASI KOMISI
                $dataMutasi = [
                    "code" => "MUTAT" . strtoupper(Str::random(5)),
                    "amount" => $dataTrx->total_amount,
                    "type" => "R", // refund,
                    "first_commission" => $firstCommission,
                    "last_commission" => $lastCommission,
                    "trx_commission_id" => $dataTrx->id,
                    "user_id" => $reseller->id,
                ];
                Mutation::create($dataMutasi);
                // UPDATE SALDO RESELLER
                $updateReseller = ["commission" => $lastCommission];
                $reseller->update($updateReseller);
            }

            // SIMPAN BUKTI REFUND JIKA ADA
            unset($data["proof_of_payment"]);
            if ($request->file("proof_of_payment")) {
                $data["proof_of_payment"] = $request->file("proof_of_payment")->store("assets/trx-commission", "public");
            }

            $dataTrx->update($data);
            DB::commit();
            return response()->json([
                "status" => "success",
                "message" => "Status Transaksi berhasil diperbarui"
            ]);
        } catch (\Throwable $err) {
            DB::rollBack();
            if ($request->file("proof_of_payment")) {
                $uploadedImg = "public/assets/trx-commission/" . $request->file("proof_of_payment")->hashName();
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
