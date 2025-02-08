<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\TrxCompensation;
use App\Models\TrxProduct;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class TrxCompensationController extends Controller
{
    public function index()
    {
        $title = "Transaksi Kompensasi";
        $user = auth()->user();
        $pageUrl = "pages.dashboard.admin.trx-compensation";

        if ($user->role == "RESELLER") {
            $pageUrl = "pages.dashboard.reseller.trx-compensation";
        }

        return view($pageUrl, compact("title"));
    }

    // HANDLER API
    public function dataTable(Request $request)
    {
        try {
            $query = TrxCompensation::with([
                "TrxProduct" => function ($query) {
                    $query->select("id", "code", "product_id")->with("Product:id,title");
                },
                "User" => function ($query) {
                    $query->select("id", "name", "code");
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
                    $action_show_proof_of_solution = $item->status == "SUCCESS" ? "<a class='dropdown-item' onclick='return getData(\"{$item->id}\", \"SHOW-PROOF-SOLUTION\");' href='javascript:void(0);' title='Bukti Refund'>Solusi Admin</a>" : "";

                    $action = " <div class='dropdown-primary dropdown open'>
                                <button class='btn btn-sm btn-primary dropdown-toggle waves-effect waves-light' id='dropdown-{$item->id}' data-toggle='dropdown' aria-haspopup='true' aria-expanded='true'>
                                    Aksi
                                </button>
                                <div class='dropdown-menu' aria-labelledby='dropdown-{$item->id}' data-dropdown-out='fadeOut'>
                                    <a class='dropdown-item' onclick='return getData(\"{$item->id}\", \"DETAIL\");' href='javascript:void(0);' title='Detail'>Detail</a>
                                    " . $action_process . "
                                    " . $action_success . "
                                    " . $action_reject . "
                                    " . $action_reason . "
                                    " . $action_show_proof_of_solution . "
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
                    $action_show_proof_of_solution = $item->status == "SUCCESS" ? "<a class='dropdown-item' onclick='return getData(\"{$item->id}\", \"SHOW-PROOF-SOLUTION\");' href='javascript:void(0);' title='Bukti Refund'>Solusi Admin</a>" : "";
                    $action = " <div class='dropdown-primary dropdown open'>
                                    <button class='btn btn-sm btn-primary dropdown-toggle waves-effect waves-light' id='dropdown-{$item->id}' data-toggle='dropdown' aria-haspopup='true' aria-expanded='true'>
                                        Aksi
                                    </button>
                                    <div class='dropdown-menu' aria-labelledby='dropdown-{$item->id}' data-dropdown-out='fadeOut'>
                                        <a class='dropdown-item' onclick='return getData(\"{$item->id}\", \"DETAIL\");' href='javascript:void(0);' title='Detail'>Detail</a>
                                        " . $action_cancel . "
                                        " . $action_reason . "
                                        " . $action_show_proof_of_solution . "
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
                $trx = "<small> 
                                <strong>Trx Code</strong> :" . ($item->TrxProduct ? $item->TrxProduct->code : '-') .  "
                                <br>
                                <strong>Product</strong> :" . ($item->TrxProduct && $item->TrxProduct->Product ? $item->TrxProduct->Product->title : '-') . "
                                <br>
                            </small>";
                $item["trx_prod"] = $item->TrxProduct;

                $item["status"] = "<span class='badge " . $classStatus . "'>" . $item["status"] . "</span>";
                $item["trx"] = $trx;
                $item['created'] = Carbon::parse($item->created_at)->addHours(7)->format('Y-m-d H:i:s');
                $item['updated'] = Carbon::parse($item->updated_at)->addHours(7)->format('Y-m-d H:i:s');
                if ($item['created'] == $item['updated']) {
                    $item['updated'] = '';
                }
                unset($item['User']);
                unset($item['TrxProduct']);
                return $item;
            });

            $queryTotal = TrxCompensation::whereBetween('created_at', [$tglAwal, $tglAkhir]);
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
            $data = $request->all();
            $rules = [
                "trx_product_id" => "required|integer",
                "description" => "required|string",
                "proof_of_constrain" =>  "required|image|max:2048|mimes:giv,svg,jpeg,png,jpg"
            ];

            $messages = [
                "trx_product_id.required" => "Data Transaksi harus dipilih",
                "trx_product_id.integer" => "Data Transaksi tidak valid",
                "proof_of_constrain.required" => "Gambar harus diisi",
                "proof_of_constrain.image" => "Gambar yang di upload tidak valid",
                "proof_of_constrain.max" => "Ukuran gambar maksimal 5MB per gambar",
                "proof_of_constrain.mimes" => "Format gambar harus gif/svg/jpeg/png/jpg"
            ];

            $validator = Validator::make($data, $rules, $messages);
            if ($validator->fails()) {
                return response()->json([
                    "status" => "error",
                    "message" => $validator->errors()->first(),
                ], 400);
            }

            // CEK DATA TRANSAKSI
            $dataTrx = TrxProduct::where("id", $data["trx_product_id"])->first();
            if (!$dataTrx) {
                return response()->json([
                    "status" => "error",
                    "message" => "Data Transaksi tidak ditemukan",
                ], 404);
            }

            // SIMPAN BUKTI PERMASALAHAN
            if ($request->file('proof_of_constrain')) {
                $data['proof_of_constrain'] = $request->file('proof_of_constrain')->store('assets/trx-compensation', 'public');
            }

            $payload = [
                "code" => "TRXCP" . strtoupper(Str::random(5)),
                "user_id" => auth()->user()->id,
                "status" => "PENDING",
                "description" => $data["description"],
                "proof_of_constrain" => $data["proof_of_constrain"],
                "trx_product_id" => $dataTrx->id
            ];

            TrxCompensation::create($payload);
            return response()->json([
                "status" => "success",
                "message" => "Komplain berhasil dibuat dan akan segera di proses oleh admin"
            ]);
        } catch (\Throwable $err) {
            if ($request->file("proof_of_constrain")) {
                $uploadedImg = "public/assets/trx-compensation/" . $request->file("proof_of_constrain")->hashName();
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
            $data = TrxCompensation::where('id', $id)->first();

            if (!$data) {
                return response()->json([
                    "status" => "error",
                    "message" => "Data tidak ditemukan",
                ], 404);
            }

            $data["proof_of_constrain"] = $data->proof_of_constrain ? Storage::url($data->proof_of_constrain) : null;
            $data["proof_of_solution"] = $data->proof_of_solution ? Storage::url($data->proof_of_solution) : null;

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
            $data = $request->all();
            $rules = [
                "id" => "required|integer",
                "status" => "required|string|in:PROCESS,SUCCESS,REJECT,CANCEL",
                "proof_of_solution" => "nullable",
                "remark" => "nullable"
            ];

            if ($request->file('proof_of_solution')) {
                $rules['proof_of_solution'] .= '|image|max:2048|mimes:giv,svg,jpeg,png,jpg';
            }

            $messages = [
                "id.required" => "Data Withdrawe harus dipilih",
                "id.integer" => "Type Withdrawe tidak valid",
                "status.required" => "Status harus diisi",
                "status.in" => "Status tidak sesuai",
                "proof_of_solution.image" => "Gambar yang di upload tidak valid",
                "proof_of_solution.max" => "Ukuran gambar maximal 2MB",
                "proof_of_solution.mimes" => "Format gambar harus giv/svg/jpeg/png/jpg",
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

            $dataTrx = TrxCompensation::find($data["id"]);
            if (!$dataTrx) {
                return response()->json([
                    "status" => "error",
                    "message" => "Data Komplain tidak ditemukan !"
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

            // SIMPAN BUKTI SOLUSI JIKA ADA
            unset($data["proof_of_solution"]);
            if ($request->file("proof_of_solution")) {
                $data["proof_of_solution"] = $request->file("proof_of_solution")->store("assets/trx-compensation", "public");
            }

            $dataTrx->update($data);
            return response()->json([
                "status" => "success",
                "message" => "Status Transaksi berhasil diperbarui"
            ]);
        } catch (\Throwable $err) {
            if ($request->file("proof_of_solution")) {
                $uploadedImg = "public/assets/trx-compensation/" . $request->file("proof_of_solution")->hashName();
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
