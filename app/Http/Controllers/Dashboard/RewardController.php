<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Reward;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class RewardController extends Controller
{
    public function index()
    {
        $title = "Master Reward";
        $resellers = User::where("role", "RESELLER")->get();
        return view("pages.dashboard.admin.reward", compact("title", "resellers"));
    }

    // HANDLER API
    public function dataTable(Request $request)
    {
        $query = Reward::query();

        if ($request->query("search")) {
            $searchValue = $request->query("search")['value'];
            $query->where(function ($query) use ($searchValue) {
                $query->where('title', 'like', '%' . $searchValue . '%');
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

            $image = '<div class="thumbnail">
                        <div class="thumb">
                            <img src="' . Storage::url($item->image) . '" alt="" width="250px" height="250px" 
                            class="img-fluid img-thumbnail" alt="' . $item->title . '">
                        </div>
                    </div>';
            $stock = '<small>
                    <strong>Sisa</strong> :' . $item->qty . '
                    <br>
                    <strong>Di Klaim</strong> :' . $item->claim . '
                    <br>
                </small>';
            $duration = '<small>
                    <strong>Start</strong> :' . Carbon::parse($item->start_date)->format('d F Y, h:i A') . '
                    <br>
                    <strong>End</strong> :' . Carbon::parse($item->end_date)->format('d F Y, h:i A') . '
                    <br>
                </small>';
            $title = "<p>" . Str::limit(strip_tags($item->title), 100) . "</p>";
            $item['action'] = $action;
            $item['image'] = $image;
            $item['stock'] = $stock;
            $item["duration"] = $duration;
            $item['is_active'] = $is_active;
            $item['title'] = $title;
            $item['type'] = $item->type == "G" ? "<small class='badge badge-primary'>Global</small>" : "<small class='badge badge-success'>VIP</small>";
            return $item;
        });

        $total = Reward::count();
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
            $reward = Reward::find($id);

            if (!$reward) {
                return response()->json([
                    "status" => "error",
                    "message" => "Data tidak ditemukan",
                ], 404);
            }

            $reward->start_date = Carbon::parse($reward->start_date)->format('Y-m-d\TH:i');
            $reward->end_date = Carbon::parse($reward->end_date)->format('Y-m-d\TH:i');

            return response()->json([
                "status" => "success",
                "data" => $reward
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
                "title" => "required|string",
                "qty" => "required|integer|min:1",
                "is_active" => "required|string|in:Y,N",
                "type" => "required|string|in:G,V",
                "image" => "required|image|max:2048|mimes:giv,svg,jpeg,png,jpg",
                "start_date" => "required|date_format:Y-m-d\TH:i",
                "end_date" => "required|date_format:Y-m-d\TH:i"
            ];

            $messages = [
                "title.required" => "Judul harus diisi",
                "qty.required" => "Quantity harus diisi",
                "qty.integer" => "Quantity tidak valid",
                "qty.min" => "Quantity minimal 1",
                "is_active.required" => "Status harus diisi",
                "is_active.in" => "Status tidak sesuai",
                "type.required" => "Type harus diisi",
                "type.in" => "Type tidak sesuai",
                "image.required" => "Gambar harus di isi",
                "image.image" => "Gambar yang di upload tidak valid",
                "image.max" => "Ukuran gambar maximal 2MB",
                "image.mimes" => "Format gambar harus gif/svg/jpeg/png/jpg",
                "start_date.required" => "Tanggal mulai harus diisi",
                "start_date.date_format" => "Format Tanggal Mulai tidak sesuai. Gunakan format Y-m-d H:i:s",
                "end_date.required" => "Tanggal akhir harus diisi",
                "end_date.date_format" => "Format Tanggal Akhir tidak sesuai. Gunakan format Y-m-d H:i:s",
            ];

            $validator = Validator::make($data, $rules, $messages);
            if ($validator->fails()) {
                return response()->json([
                    "status" => "error",
                    "message" => $validator->errors()->first(),
                ], 400);
            }

            if ($request->file('image')) {
                $data['image'] = $request->file('image')->store('assets/reward', 'public');
            }
            unset($data['id']);

            $startDate = Carbon::parse($request->start_date);
            $endDate = Carbon::parse($request->end_date);

            $data["start_date"] = $startDate;
            $data["end_date"] = $endDate;

            Reward::create($data);
            return response()->json([
                "status" => "success",
                "message" => "Data berhasil dibuat"
            ]);
        } catch (\Exception $err) {
            if ($request->file("image")) {
                $uploadedImg = "public/assets/reward" . $request->image->hashName();
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

    public function update(Request $request)
    {
        try {
            $data = $request->all();
            $rules = [
                "id" => "required|integer",
                "title" => "required|string",
                "qty" => "required|integer|min:0",
                "is_active" => "required|string|in:Y,N",
                "type" => "required|string|in:G,V",
                "image" => "nullable",
                "start_date" => "required|date_format:Y-m-d\TH:i",
                "end_date" => "required|date_format:Y-m-d\TH:i"
            ];

            if ($request->file('image')) {
                $rules['image'] .= '|image|max:2048|mimes:giv,svg,jpeg,png,jpg';
            }

            $messages = [
                "id.required" => "Data ID harus diisi",
                "id.integer" => "Type ID tidak sesuai",
                "title.required" => "Judul harus diisi",
                "qty.required" => "Quantity harus diisi",
                "qty.integer" => "Quantity tidak valid",
                "qty.min" => "Quantity tidak boleh minus",
                "is_active.required" => "Status harus diisi",
                "is_active.in" => "Status tidak sesuai",
                "type.required" => "Type harus diisi",
                "type.in" => "Type tidak sesuai",
                "image.required" => "Gambar harus di isi",
                "image.image" => "Gambar yang di upload tidak valid",
                "image.max" => "Ukuran gambar maximal 2MB",
                "image.mimes" => "Format gambar harus gif/svg/jpeg/png/jpg",
                "start_date.required" => "Tanggal mulai harus diisi",
                "start_date.date_format" => "Format Tanggal Mulai tidak sesuai. Gunakan format Y-m-d H:i:s",
                "end_date.required" => "Tanggal akhir harus diisi",
                "end_date.date_format" => "Format Tanggal Akhir tidak sesuai. Gunakan format Y-m-d H:i:s",
            ];

            $validator = Validator::make($data, $rules, $messages);
            if ($validator->fails()) {
                return response()->json([
                    "status" => "error",
                    "message" => $validator->errors()->first(),
                ], 400);
            }

            $reward = Reward::find($data['id']);
            if (!$reward) {
                return response()->json([
                    "status" => "error",
                    "message" => "Data tidak ditemukan"
                ], 404);
            }

            // delete undefined data image
            unset($data["image"]);
            if ($request->file("image")) {
                $oldImagePath = "public/" . $reward->image;
                if (Storage::exists($oldImagePath)) {
                    Storage::delete($oldImagePath);
                }
                $data["image"] = $request->file("image")->store("assets/reward", "public");
            }

            $startDate = Carbon::parse($request->start_date);
            $endDate = Carbon::parse($request->end_date);

            $data["start_date"] = $startDate;
            $data["end_date"] = $endDate;

            $reward->update($data);
            return response()->json([
                "status" => "success",
                "message" => "Data berhasil diperbarui"
            ]);
        } catch (\Exception $err) {
            if ($request->file("image")) {
                $uploadedImg = "public/assets/reward" . $request->image->hashName();
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

            $reward = Reward::find($data['id']);
            if (!$reward) {
                return response()->json([
                    "status" => "error",
                    "message" => "Data tidak ditemukan"
                ], 404);
            }
            $reward->update($data);
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
            $reward = Reward::find($id);
            if (!$reward) {
                return response()->json([
                    "status" => "error",
                    "message" => "Data tidak ditemukan"
                ], 404);
            }
            $oldImagePath = "public/" . $reward->image;
            if (Storage::exists($oldImagePath)) {
                Storage::delete($oldImagePath);
            }

            $reward->delete();
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
