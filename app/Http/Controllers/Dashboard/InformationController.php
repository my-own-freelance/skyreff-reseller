<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Information;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class InformationController extends Controller
{
    public function index()
    {
        $title = "Master Informasi";
        return view("pages.dashboard.admin.information", compact("title"));
    }

    // HANDLER API
    public function dataTable(Request $request)
    {
        $query = Information::query();

        if ($request->query("search")) {
            $searchValue = $request->query("search")['value'];
            $query->where(function ($query) use ($searchValue) {
                $query->where('subject', 'like', '%' . $searchValue . '%')
                    ->Orwhere('message', 'like', '%' . $searchValue . '%');
            });
        }

        $recordsFiltered = $query->count();
        $data = $query->orderBy('id', 'desc')
            ->skip($request->query('start'))
            ->limit($request->query('length'))
            ->get();

        $output = $data->map(function ($item) {
            $action = " <div class='dropdown-primary dropdown open'>
                            <button class='btn btn-sm btn-primary dropdown-toggle waves-effect waves-light' id='dropdown-{$item->id}' data-toggle='dropdown' aria-haspopup='true' aria-expanded='true'>
                                Aksi
                            </button>
                            <div class='dropdown-menu' aria-labelledby='dropdown-{$item->id}' data-dropdown-out='fadeOut'>
                                <a class='dropdown-item' onclick='return getData(\"{$item->id}\");' href='javascript:void(0);' title='Edit'>Edit</a>
                                <a class='dropdown-item' onclick='return removeData(\"{$item->id}\");' href='javascript:void(0)' title='Hapus'>Hapus</a>
                            </div>
                        </div>";

            $is_active = $item->is_active == 'Y' ? '
                <div class="text-center">
                    <span class="label-switch">Publish</span>
                </div>
                <div class="input-row">
                    <div class="toggle_status on">
                        <input type="checkbox" onclick="return updateStatus(\'' . $item->id . '\', \'Draft\');" />
                        <span class="slider"></span>
                    </div>
                </div>' :
                '<div class="text-center">
                    <span class="label-switch">Draft</span>
                </div>
                <div class="input-row">
                    <div class="toggle_status off">
                        <input type="checkbox" onclick="return updateStatus(\'' . $item->id . '\', \'Publish\');" />
                        <span class="slider"></span>
                    </div>
                </div>';


            $subject = "<p>" . Str::limit(strip_tags($item->subject), 100) . "</p>";
            $message = "<p>" . Str::limit(strip_tags($item->message), 150) . "</p>";
            $type = "";
            switch ($item->type) {
                case "P":
                    $type = "<span class='badge badge-primary'>Primary</span>";
                    break;
                case "I":
                    $type = "<span class='badge badge-info'>Information</span>";
                    break;
                case "W":
                    $type = "<span class='badge badge-warning'>Warning</span>";
                    break;
                case "D":
                    $type = "<span class='badge badge-danger'>Danger</span>";
                    break;
                default:
                    $type = "<span class='badge badge-info'>Information</span>";
                    break;
            }
            $item['type'] = $type;
            $item['action'] = $action;
            $item['is_active'] = $is_active;
            $item['subject'] = $subject;
            $item['message'] = $message;
            return $item;
        });

        $total = Information::count();
        return response()->json([
            'draw' => $request->query('draw'),
            'recordsFiltered' => $recordsFiltered,
            'recordsTotal' => $total,
            'data' => $output,
        ]);
    }

    public function getDetail($id)
    {
        try {
            $information = Information::find($id);

            if (!$information) {
                return response()->json([
                    "status" => "error",
                    "message" => "Data tidak ditemukan",
                ], 404);
            }

            return response()->json([
                "status" => "success",
                "data" => $information
            ]);
        } catch (\Exception $err) {
            return response()->json([
                "status" => "error",
                "message" => $err->getMessage()
            ], 500);
        }
    }

    public function create(Request $request)
    {
        try {
            $data = $request->all();
            $rules = [
                "subject" => "required|string",
                "message" => "required|string",
                "type" => "required|string|in:P,I,W,D",
                "is_active" => "required|string|in:Y,N",
            ];

            $messages = [
                "subject.required" => "Judul harus diisi",
                "message.required" => "Pesan harus diisi",
                "type.required" => "Type informasi harus diisi",
                "type.in" => "Type informasi tidak sesuai",
                "is_active.required" => "Status harus diisi",
                "is_active.in" => "Status tidak sesuai",
            ];

            $validator = Validator::make($data, $rules, $messages);
            if ($validator->fails()) {
                return response()->json([
                    "status" => "error",
                    "message" => $validator->errors()->first(),
                ], 400);
            }

            unset($data['id']);

            Information::create($data);
            return response()->json([
                "status" => "success",
                "message" => "Data berhasil dibuat"
            ]);
        } catch (\Exception $err) {
            return response()->json([
                "status" => "error",
                "message" => $err->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request)
    {
        try {
            $data = $request->all();
            $rules = [
                "id" => "required|integer",
                "subject" => "required|string",
                "message" => "required|string",
                "type" => "required|string|in:P,I,W,D",
                "is_active" => "required|string|in:Y,N",
            ];

            $messages = [
                "id.required" => "Data ID harus diisi",
                "id.integer" => "Type ID tidak sesuai",
                "subject.required" => "Judul harus diisi",
                "message.required" => "Pesan harus diisi",
                "type.required" => "Type informasi harus diisi",
                "type.in" => "Type informasi tidak sesuai",
                "is_active.required" => "Status harus diisi",
                "is_active.in" => "Status tidak sesuai",
            ];

            $validator = Validator::make($data, $rules, $messages);
            if ($validator->fails()) {
                return response()->json([
                    "status" => "error",
                    "message" => $validator->errors()->first(),
                ], 400);
            }

            $information = Information::find($data['id']);
            if (!$information) {
                return response()->json([
                    "status" => "error",
                    "message" => "Data tidak ditemukan"
                ], 404);
            }

            $information->update($data);
            return response()->json([
                "status" => "success",
                "message" => "Data berhasil diperbarui"
            ]);
        } catch (\Exception $err) {
            return response()->json([
                "status" => "error",
                "message" => $err->getMessage(),
            ], 500);
        }
    }

    public function updateStatus(Request $request)
    {
        try {
            $data = $request->all();
            $rules = [
                "id" => "required|integer",
                "is_active" => "required|string|in:Y,N",
            ];

            $messages = [
                "id.required" => "Data ID harus diisi",
                "id.integer" => "Type ID tidak sesuai",
                "is_active.required" => "Status harus diisi",
                "is_active.in" => "Status tidak sesuai",
            ];

            $validator = Validator::make($data, $rules, $messages);
            if ($validator->fails()) {
                return response()->json([
                    "status" => "error",
                    "message" => $validator->errors()->first(),
                ], 400);
            }

            $information = Information::find($data['id']);
            if (!$information) {
                return response()->json([
                    "status" => "error",
                    "message" => "Data tidak ditemukan"
                ], 404);
            }
            $information->update($data);
            return response()->json([
                "status" => "success",
                "message" => "Status berhasil diperbarui"
            ]);
        } catch (\Exception $err) {
            return response()->json([
                "status" => "error",
                "message" => $err->getMessage(),
            ], 500);
        }
    }

    public function destroy(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), ["id" => "required|integer"], [
                "id.required" => "Data ID harus diisi",
                "id.integer" => "Type ID tidak valid"
            ]);

            if ($validator->fails()) {
                return response()->json([
                    "status" => "error",
                    "message" => $validator->errors()->first()
                ], 400);
            }

            $id = $request->id;
            $information = Information::find($id);
            if (!$information) {
                return response()->json([
                    "status" => "error",
                    "message" => "Data tidak ditemukan"
                ], 404);
            }

            $information->delete();
            return response()->json([
                "status" => "success",
                "message" => "Data berhasil dihapus"
            ]);
        } catch (\Exception $err) {
            return response()->json([
                "status" => "error",
                "message" => $err->getMessage()
            ], 500);
        }
    }
}
