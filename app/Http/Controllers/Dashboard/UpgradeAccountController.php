<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\TrxUpgrade;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class UpgradeAccountController extends Controller
{
    public function index()
    {
        $title = "Transaksi Upgrade Akun";
        $pageUrl = "pages.dashboard.admin.trx-upgrade";

        return view($pageUrl, compact("title"));
    }

    // API
    public function dataTable(Request $request)
    {
        try {
            $query = TrxUpgrade::with([
                "User" => function ($query) {
                    $query->select("id", "code", "name");
                }
            ]);

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
            $output = $data->map(function ($item) {
                $action_process = $item->status == "PENDING" ? "<a class='dropdown-item' onclick='return changeStatus(\"{$item->id}\", \"PROCESS\");' href='javascript:void(0);' title='In Process'>In Process</a>" : "";
                $action_success = $item->status == "PENDING" || $item->status == "PROCESS" ? "<a class='dropdown-item' onclick='return changeStatus(\"{$item->id}\", \"SUCCESS\");' href='javascript:void(0);' title='Success'>Success</a>" : "";
                $action_reject = $item->status == "PENDING" || $item->status == "PROCESS" ? "<a class='dropdown-item' onclick='return changeStatus(\"{$item->id}\", \"REJECT\");' href='javascript:void(0);' title='Reject'>Reject</a>" : "";

                $action = " <div class='dropdown-primary dropdown open'>
                                <button class='btn btn-sm btn-primary dropdown-toggle waves-effect waves-light' id='dropdown-{$item->id}' data-toggle='dropdown' aria-haspopup='true' aria-expanded='true'>
                                    Aksi
                                </button>
                                <div class='dropdown-menu' aria-labelledby='dropdown-{$item->id}' data-dropdown-out='fadeOut'>
                                    " . $action_process . "
                                    " . $action_success . "
                                    " . $action_reject . "
                                </div>
                            </div>";

                if (in_array($item["status"], ["REJECT", "CANCEL", "SUCCESS"])) {
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
                $item['created'] = Carbon::parse($item->created_at)->addHours(7)->format('Y-m-d H:i:s');
                $item['updated'] = Carbon::parse($item->updated_at)->addHours(7)->format('Y-m-d H:i:s');
                if ($item['created'] == $item['updated']) {
                    $item['updated'] = '';
                }
                unset($item['User']);
                return $item;
            });

            $queryTotal = TrxUpgrade::whereBetween('created_at', [$tglAwal, $tglAkhir]);

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
            $data = $request->all();
            $rules = [
                "user_id" => "integer|required",
            ];

            $messages = [
                "user_id.integer" => "Data reseller tidak valid",
                "user_id.requred" => "Data reseller harus disertakan",
            ];

            $validator = Validator::make($data, $rules, $messages);
            if ($validator->fails()) {
                return response()->json([
                    "status" => "error",
                    "message" => $validator->errors()->first(),
                ], 400);
            }

            $reseller = User::where("id", auth()->user()->id)->first();
            if ($reseller->role != "RESELLER") {
                return response()->json([
                    "status" => "error",
                    "message" => "Hanya reseller yg dapat melakukan request upgrade account"
                ], 400);
            }

            if ($reseller->level == "VIP") {
                return response()->json([
                    "status" => "error",
                    "message" => "Akun level anda sudah VIP dan tidak bisa di upgrade lagi"
                ], 400);
            }

            if ($reseller->id != $data["user_id"]) {
                return response()->json([
                    "status" => "error",
                    "message" => "Permintaan anda tidak valid"
                ]);
            }

            // CEK REQUEST LAMA YG STATUS NYA MASIH PENDING/PROCESS
            $checkRequest = TrxUpgrade::where("user_id", $reseller->id)->whereIn("status", ["PENDING", "PROCESS"])->first();
            if ($checkRequest) {
                return response()->json([
                    "status" => "error",
                    "message" => "Anda sudah pernah melakukan request Upgrade, silahkan tunggu di proses oleh admin dulu"
                ], 400);
            }

            TrxUpgrade::create([
                "status" => "PENDING",
                "user_id" => $reseller->id,
                "remark" => "Request Upgrade Account Level to VIP"
            ]);

            return response()->json([
                "status" => "success",
                "message" => "Request Upgrade diajukan, silahkan tunggu admin untuk memproses"
            ]);
        } catch (\Throwable $err) {
            return response()->json([
                "status" => "error",
                "message" => $err->getMessage()
            ], 500);
        }
    }

    public function updateStatus(Request $request)
    {
        try {
            DB::beginTransaction();
            $data = $request->all();
            $rules = [
                "id" => "required|integer",
                "status" => "required|string|in:PROCESS,SUCCESS,REJECT,CANCEL",
            ];

            $messages = [
                "id.required" => "Data Withdrawe harus dipilih",
                "id.integer" => "Type Withdrawe tidak valid",
                "status.required" => "Status harus diisi",
                "status.in" => "Status tidak sesuai",
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

            $dataTrx = TrxUpgrade::find($data["id"]);
            if (!$dataTrx) {
                return response()->json([
                    "status" => "error",
                    "message" => "Data Upgrade akun tidak ditemukan !"
                ], 404);
            }

            // VALIDASI TRX . JIKA SUDAH REJECT / CANCEL TIDAK BOLEH DIUBAH LAGI STATUSNYA
            if (in_array($dataTrx->status, ['REJECT', 'CANCEL'])) {
                return response()->json([
                    "status" => "error",
                    "message" => "Status withdraw sudah tidak bisa diubah"
                ], 400);
            }

            $reseller = User::find($dataTrx->user_id);
            if (!$reseller) {
                return response()->json([
                    "status" => "error",
                    "message" => "Data Reseller atas transaksi ini tidak ditemukan"
                ], 404);
            }

            // UPGRADE LEVEL RESELLER
            if ($data["status"] == "SUCCESS") {
                $reseller->update(["level" => "VIP"]);
            }

            $dataTrx->update($data);
            DB::commit();
            return response()->json([
                "status" => "success",
                "message" => "Status Request Upgrade Akun berhasil diperbarui"
            ]);
        } catch (\Throwable $err) {
            DB::rollBack();
            return response()->json([
                "status" => "error",
                "message" => $err->getMessage(),
            ], 500);
        }
    }
}
