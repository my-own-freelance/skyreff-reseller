<?php

namespace App\Http\Controllers\Dashboard;

use App\Exports\TrxRewardExport;
use App\Http\Controllers\Controller;
use App\Models\Reward;
use App\Models\TrxReward;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class TrxRewardController extends Controller
{
    public function index()
    {
        $title = "Transaksi Reward";
        $user = Auth::user();
        $pageUrl = $user->role == "ADMIN" ? "pages.dashboard.admin.trx-reward" :  "pages.dashboard.reseller.trx-reward";

        return view($pageUrl, compact("title"));
    }


    public function export(Request $request)
    {
        return Excel::download(new TrxRewardExport($request), 'Transaksi Reward.xlsx');
    }

    // API
    public function dataTable(Request $request)
    {
        try {
            $query = TrxReward::with([
                "User" => function ($query) {
                    $query->select("id", "code", "name");
                },
                "Reward" => function ($query) {
                    $query->select("id", "title");
                }
            ])->whereNot("reward_id", null);

            // FILTER BY RESELLER ID
            $user = auth()->user();
            if ($user->role == "RESELLER") {
                $query->where('user_id', $user->id);
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
                    $action_show_proof_of_payment = $item->status == "SUCCESS" ? "<a class='dropdown-item' onclick='return getData(\"{$item->id}\", \"SHOW-PROOF-ACCEPTION\");' href='javascript:void(0);' title='Bukti Hadiah'>Bukti Hadiah</a>" : "";

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
                    $action_reason =  $item->status == "REJECT" ? "<a class='dropdown-item' onclick='return getData(\"{$item->id}\", \"SHOW-REASON-REJECT\");' href='javascript:void(0);' title='Alasan Ditolak'>Alasan Ditolak</a>" : "";
                    $action_show_proof_of_payment = $item->status == "SUCCESS" ? "<a class='dropdown-item' onclick='return getData(\"{$item->id}\", \"SHOW-PROOF-ACCEPTION\");' href='javascript:void(0);' title='Bukti Hadiah'>Bukti Hadiah</a>" : "";
                    $action = " <div class='dropdown-primary dropdown open'>
                                    <button class='btn btn-sm btn-primary dropdown-toggle waves-effect waves-light' id='dropdown-{$item->id}' data-toggle='dropdown' aria-haspopup='true' aria-expanded='true'>
                                        Aksi
                                    </button>
                                    <div class='dropdown-menu' aria-labelledby='dropdown-{$item->id}' data-dropdown-out='fadeOut'>
                                        " . $action_reason . "
                                        " . $action_show_proof_of_payment . "
                                    </div>
                                </div>";

                    if (!in_array($item->status, ["REJECT", "SUCCESS"])) {
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
                $item["reward"] = $item->Reward ? $item->Reward->title : '';
                $item['created'] = Carbon::parse($item->created_at)->addHours(7)->format('Y-m-d H:i:s');
                $item['updated'] = Carbon::parse($item->updated_at)->addHours(7)->format('Y-m-d H:i:s');
                if ($item['created'] == $item['updated']) {
                    $item['updated'] = '';
                }
                unset($item['User']);
                unset($item['Reward']);
                return $item;
            });

            $queryTotal = TrxReward::whereBetween('created_at', [$tglAwal, $tglAkhir]);
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
            $user = User::where("id", auth()->user()->id)->first();
            $userId = $user->id;

            // cari data transaksi hari ini
            $tglAwal = Carbon::now('UTC')->startOfDay()->subHour(7)->toDateTimeString(); // dikurangi 7 jam mengikuti waktu utc
            $tglAkhir = Carbon::now('UTC')->endOfDay()->subHour(7)->toDateTimeString(); // dikurangi 7 jam mengikuti waktu utc
            $alreadyClaim = TrxReward::whereBetween('created_at', [$tglAwal, $tglAkhir])->first();
            if ($alreadyClaim) {
                return response()->json([
                    "status" => "error",
                    "message" => "Kesempatan anda untuk claim reward sudah habis"
                ], 400);
            }

            // CARI DATA REWARD YG TERSEDIA
            $waktuSekarang = Carbon::now()->addHour(7)->toDateTimeString();
            $rewards = Reward::where('start_date', '<=', $waktuSekarang)
                ->where('end_date', '>', $waktuSekarang)
                ->where(function ($query) use ($userId) {
                    $query->whereNull('reseller_list') // Jika reseller_list kosong, siapa saja bisa klaim
                        ->orWhereRaw('JSON_CONTAINS(reseller_list, ?)', [json_encode(strval($userId))]); // Jika ada, cek apakah user termasuk
                })
                ->where('qty', ">", 0)
                ->where('is_active', 'Y')
                ->get();

            // CEK APAKAH REWARD KOSONG ATAU TIDAK
            // JIKA REWARD KOSONG, BUAT TRANSAKSI DGN REWARD_ID = NULL . SBG PENDATAAN AGAR RESELLER TIDAK BISA CLAIM REWARD LAGI DI HARI YG SAMA
            if ($rewards->isEmpty()) {
                // BUAT HISTORY TRANSAKSI REWARD DENGAN REWARD_ID = NULL
                TrxReward::create([
                    'user_id' => $userId,
                    'reward_id' => null,
                    'status' => 'PENDING',
                ]);
                DB::commit();
                return response()->json([
                    "status" => "success",
                    "message" => "Opps, Sayang sekali kamu sedang ZONKK!!!",
                    "remark" => "Reward Kosong",
                    "waktu" => $waktuSekarang
                ], 400);
            }

            // CEK APAKAH REWARD_ID SUDAH PERNAH DI CLAIM DI HARI" SEBELUMNYA ATAU BELUM
            $availableRewards = $rewards->filter(function ($reward) use ($userId) {
                return !TrxReward::where('user_id', $userId)
                    ->where('reward_id', $reward->id)
                    ->exists();
            });

            // Jika semua reward sudah pernah diklaim, kembalikan response
            if ($availableRewards->isEmpty()) {
                // BUAT HISTORY TRANSAKSI REWARD DENGAN REWARD_ID = NULL
                TrxReward::create([
                    'user_id' => $userId,
                    'reward_id' => null,
                    'status' => 'PENDING'
                ]);
                DB::commit();
                return response()->json([
                    'success' => false,
                    'message' => 'Opps, Sayang sekali kamu sedang ZONKK!!!',
                    "remark" => "Reward yg tersedia sudah pernah di claim semua",
                    "waktu" => $waktuSekarang
                ], 400);
            }

            // Ambil satu reward secara acak
            $selectedReward = $availableRewards->shuffle()->first();
            TrxReward::create([
                "user_id" => $userId,
                "reward_id" => $selectedReward->id,
                "status" => "PENDING"
            ]);

            // UPDATE QTY DAN CLAIM DATA REWARD
            $dataReward = Reward::where("id", $selectedReward->id)->first();
            if ($dataReward) {
                $updatedData = ["qty" => $dataReward->qty - 1, "claim" => $dataReward->claim + 1];
                $dataReward->update($updatedData);
            }

            DB::commit();
            return response()->json([
                "status" => "success",
                "message" => "Selamat anda mendapatkan reward " . $selectedReward->title,
                "waktu" => $waktuSekarang,
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
            $data = TrxReward::where('id', $id)->first();

            if (!$data) {
                return response()->json([
                    "status" => "error",
                    "message" => "Data tidak ditemukan",
                ], 404);
            }

            $data["proof_of_payment"] = $data->proof_of_acception ? Storage::url($data->proof_of_acception) : null;

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
                "proof_of_acception" => "nullable",
                "remark" => "nullable"
            ];

            if ($request->file('proof_of_acception')) {
                $rules['proof_of_acception'] .= '|image|max:2048|mimes:giv,svg,jpeg,png,jpg';
            }

            $messages = [
                "id.required" => "Data Withdrawe harus dipilih",
                "id.integer" => "Type Withdrawe tidak valid",
                "status.required" => "Status harus diisi",
                "status.in" => "Status tidak sesuai",
                "proof_of_acception.image" => "Gambar yang di upload tidak valid",
                "proof_of_acception.max" => "Ukuran gambar maximal 2MB",
                "proof_of_acception.mimes" => "Format gambar harus giv/svg/jpeg/png/jpg",
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

            $dataTrx = TrxReward::find($data["id"]);
            if (!$dataTrx) {
                return response()->json([
                    "status" => "error",
                    "message" => "Data Withdraw tidak ditemukan !"
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

            // UPDATE QTY DAN CLAIM DATA REWARD
            if (in_array($data["status"], ["REJECT", "CANCEL"])) {
                $reward = Reward::where("id", $dataTrx->reward_id)->first();
                if ($reward) {
                    $updatedData = ["qty" => $reward->qty + 1, "claim" => $reward->claim - 1];
                    $reward->update($updatedData);
                }
            }

            // SIMPAN BUKTI REFUND JIKA ADA
            unset($data["proof_of_acception"]);
            if ($request->file("proof_of_acception")) {
                $data["proof_of_acception"] = $request->file("proof_of_acception")->store("assets/trx-reward", "public");
            }

            $dataTrx->update($data);
            DB::commit();
            return response()->json([
                "status" => "success",
                "message" => "Status Transaksi berhasil diperbarui"
            ]);
        } catch (\Throwable $err) {
            DB::rollBack();
            if ($request->file("proof_of_acception")) {
                $uploadedImg = "public/assets/trx-reward/" . $request->file("proof_of_acception")->hashName();
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
