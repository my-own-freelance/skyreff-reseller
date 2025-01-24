<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\District;
use App\Models\Province;
use App\Models\SubDistrict;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ResellerController extends Controller
{
    public function indexReseller()
    {
        $title = "Data Reseller";
        return view("pages.dashboard.admin.reseller.index", compact('title'));
    }

    public function resellerWaiting()
    {
        return view("pages.dashboard.admin.reseller.waiting");
    }

    public function resellerRegular()
    {
        return view("pages.dashboard.admin.reseller.regular");
    }

    public function resellerVip()
    {
        return view("pages.dashboard.admin.reseller.vip");
    }

    public function resellerDeleted()
    {
        return view("pages.dashboard.admin.reseller.deleted");
    }


    // HANDLER API
    public function resellerDataTable(Request $request)
    {
        $query = User::where("role", "RESELLER");

        if ($request->query('search')) {
            $searchValue = $request->query("search")['value'];
            $query->where(function ($query) use ($searchValue) {
                $query->where('name', 'like', '%' . $searchValue . '%')
                    ->orWhere('username', 'like', '%' . $searchValue . '%')
                    ->orWhere('code', 'like', '%' . $searchValue . '%')
                    ->orWhere('phone_number', 'like', '%' . $searchValue . '%');
            });
        }

        // filter level
        if ($request->query("level") && $request->query('level') != "") {
            $query->where('level', $request->query('level'));
        }

        // filter status
        if ($request->query("is_active") && $request->query('is_active') != "") {
            $query->where('is_active', $request->query('is_active'));
        }

        // filter data user yg soft delete
        if ($request->query('status_data') && $request->query('status_data') == 'DELETED') {
            $query->onlyTrashed();
        }

        $recordsFiltered = $query->count();

        $data = $query->orderBy('name', 'asc')
            ->skip($request->query('start'))
            ->limit($request->query('length'))
            ->get();

        $output = $data->map(function ($item) {
            $user = auth()->user();
            $action_edit = !$item->deleted_at && $user->role == "ADMIN" ? "<a class='dropdown-item' onclick='return getData(\"{$item->id}\", \"edit\");' href='javascript:void(0);' title='Edit'>Edit</a>" : "";
            $action_soft_delete = !$item->deleted_at && $user->role == "ADMIN"  ? "<a class='dropdown-item' onclick='return softDelete(\"{$item->id}\", \"" . strtolower($item->level) . "\", \"deleted\");' href='javascript:void(0)' title='Hapus Sementara'>Hapus</a>" : "";
            $action_restore_soft_delete = $item->deleted_at != null &&  $user->role == "ADMIN" ? "<a class='dropdown-item' onclick='return restoreData(\"{$item->id}\", \"deleted\",  \"" . strtolower($item->level) . "\");' href='javascript:void(0)' title='Hapus'>Restore</a>" : "";

            $action = "<div class='dropdown-primary dropdown open'>
                            <button class='btn btn-sm btn-primary dropdown-toggle waves-effect waves-light' id='dropdown-{$item->id}' data-toggle='dropdown' aria-haspopup='true' aria-expanded='true'>
                                Aksi
                            </button>
                            <div class='dropdown-menu' aria-labelledby='dropdown-{$item->id}' data-dropdown-out='fadeOut'>
                                " . $action_edit . "
                                " . $action_soft_delete . "
                                " . $action_restore_soft_delete . "
                            </div>
                        </div>";


            $is_active = $item->is_active == 'Y' ? '
                    <div class="text-center">
                        <span class="label-switch">Active</span>
                    </div>
                    <div class="input-row">
                        <div class="toggle_status on">
                            <input type="checkbox" onclick="return updateStatus(\'' . $item->id . '\', \'Disabled\', \'' . strtolower($item->level) . '\');" />
                            <span class="slider"></span>
                        </div>
                    </div>' :
                '
                    <div class="text-center">
                        <span class="label-switch">Disabled</span>
                    </div>
                    <div class="input-row">
                        <div class="toggle_status off">
                            <input type="checkbox" onclick="return updateStatus(\'' . $item->id . '\', \'Active\', \'' . strtolower($item->level) . '\');" />
                            <span class="slider"></span>
                        </div>
                    </div>';
            $level = $item->level ? ($item->level == "REGULAR" ? "<p class='badge badge-primary'>REGULAR</p>" : "<p class='badge badge-success'>VIP</p>") : "-";
            $code = '<strong>' . $item->code . '</strong>';

            $item['action'] = $action;
            $item['is_active'] = $is_active;
            $item["level"] = $level;
            $item['debt_limit'] = 'Rp. ' . number_format($item->debt_limit, 0, ',', '.');
            $item['total_debt'] = 'Rp. ' . number_format($item->total_debt, 0, ',', '.');
            $item['commission'] = 'Rp. ' . number_format($item->commission, 0, ',', '.');
            $item['created'] = Carbon::parse($item->created_at)->addHours(7)->format('Y-m-d H:i:s');
            $item['code'] = $code;
            return $item;
        });

        // filter level
        $queryTotal = User::where("role", "RESELLER");
        if ($request->query("level") && $request->query('level') != "") {
            $queryTotal->where('level', $request->query('level'));
        }

        // filter status
        if ($request->query("is_active") && $request->query('is_active') != "") {
            $queryTotal->where('is_active', $request->query('is_active'));
        }

        // filter data user yg soft delete
        if ($request->query('status_data') && $request->query('status_data') == 'DELETED') {
            $queryTotal->onlyTrashed();
        }

        $total = $queryTotal->count();
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
            $reseller = User::find($id);

            if (!$reseller) {
                return response()->json([
                    "status" => "error",
                    "message" => "Data tidak ditemukan",
                ], 404);
            }

            if ($reseller->image) {
                $reseller["image"] = url("/") . Storage::url($reseller->image);
            }

            return response()->json([
                "status" => "success",
                "data" => $reseller
            ]);
        } catch (\Exception $err) {
            return response()->json([
                "status" => "error",
                "message" => $err->getMessage()
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

            $user = User::find($data['id']);
            if (!$user) {
                return response()->json([
                    "status" => "error",
                    "message" => "Data tidak ditemukan"
                ], 404);
            }
            $user->update($data);
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

    public function create(Request $request)
    {
        try {
            $data = $request->all();
            $data["phone_number"] = preg_replace('/^08/', '628', $data['phone_number']);
            $rules = [
                "name" => "required|string",
                "username" => "required|string|unique:users",
                "password" => "required|string|min:5",
                "phone_number" => "required|string|digits_between:10,15",
                "is_active" => "required|string|in:Y,N",
                "level" => "required|string|in:REGULAR,VIP",
                "debt_limit" => "required|integer|min:0",
                "image" => "required|image|max:2048|mimes:giv,svg,jpeg,png,jpg",
                "province_id" => "required|integer",
                "district_id" => "required|integer",
                "sub_district_id" => "required|integer",
                "address" => "required|string",
            ];

            $messages = [
                "name.required" => "Nama harus diisi",
                "username.required" => "Username harus diisi",
                "username.unique" => "Username sudah digunakan",
                "phone_number.required" => "Nomor telepon harus diisi",
                "phone_number.unique" => "Nomor telepon sudah digunakan",
                "phone_number.digits_between" => "Nomor telepon harus memiliki panjang antara 10 hingga 15 karakter",
                "password.required" => "Password harus diisi",
                "password.min" => "Password minimal 5 karakter",
                "is_active.required" => "Status harus diisi",
                "is_active.in" => "Status tidak sesuai",
                "level.required" => "Level harus diisi",
                "level.in" => "Level tidak valid",
                "debt_limit.required" => "Limit pihutang harus diisi",
                "debt_limit.integer" => "Limit pihutang tidak valid",
                "debt_limit.min" => "Limit pihutang tidak boleh minus",
                "image.required" => "Gambar harus di isi",
                "image.image" => "Gambar yang di upload tidak valid",
                "image.max" => "Ukuran gambar maximal 2MB",
                "image.mimes" => "Format gambar harus giv/svg/jpeg/png/jpg",
                "province_id.required" => "Provinsi harus diisi",
                "province_id.integer" => "Provinsi tidak valid",
                "district_id.required" => "Kabupaten harus diisi",
                "district_id.integer" => "Kabupaten tidak valid",
                "sub_district_id.required" => "Kecamatan harus diisi",
                "sub_district_id.integer" => "Kecamatan tidak valid",
                "address.required" => "Alamat harus diisi",
                "description.required" => "Deskripsi harus diisi"
            ];

            $validator = Validator::make($data, $rules, $messages);
            if ($validator->fails()) {
                return response()->json([
                    "status" => "error",
                    "message" => $validator->errors()->first(),
                ], 400);
            }

            // cek provinsi
            $province = Province::find($request->province_id);
            if (!$province) {
                return response()->json([
                    "status" => "error",
                    "message" => "Provinsi tidak ditemukan",
                ], 400);
            }

            // cek kabupaten
            $district = District::find($request->district_id);
            if (!$district) {
                return response()->json([
                    "status" => "error",
                    "message" => "Kabupaten tidak ditemukan",
                ], 400);
            }

            // cek kecamatan
            $subDistrict = SubDistrict::find($request->sub_district_id);
            if (!$subDistrict) {
                return response()->json([
                    "status" => "error",
                    "message" => "Kecamatan tidak ditemukan",
                ], 400);
            }
            
            if ($request->file('image')) {
                $data['image'] = $request->file('image')->store('assets/user', 'public');
            }

            $data["password"] = Hash::make($request->password);
            $data["role"] = "RESELLER";
            $data["code"] = "RES" . strtoupper(Str::random(7));
            User::create($data);

            return response()->json([
                "status" => "success",
                "message" => "Berhasil menambahkan data pengguna"
            ]);
        } catch (\Exception $err) {
            if ($request->file("image")) {
                $uploadedImg = "public/assets/user" . $request->image->hashName();
                if (Storage::exists($uploadedImg)) {
                    Storage::delete($uploadedImg);
                }
            }
            return response()->json([
                "status" => "error",
                "message" => $err->getMessage()
            ], 500);
        }
    }

    public function update(Request $request)
    {
        try {
            $data = $request->all();
            $rules = [
                "id" => "required|integer",
                "name" => "required|string",
                "password" => "nullable",
                "phone_number" => "required|string|digits_between:10,15",
                "is_active" => "required|string|in:Y,N",
                "level" => "required|string|in:REGULAR,VIP",
                "debt_limit" => "required|integer|min:0",
                "image" => "nullable",
                "province_id" => "required|integer",
                "district_id" => "required|integer",
                "sub_district_id" => "required|integer",
                "address" => "required|string",
            ];

            if ($data && $data['password'] != "") {
                $rules['password'] .= "|string|min:5";
            }

            if ($request->file('image')) {
                $rules['image'] .= '|image|max:1024|mimes:giv,svg,jpeg,png,jpg';
            }

            $messages = [
                "id.required" => "Data ID harus diisi",
                "id.integer" => "Type ID tidak sesuai",
                "username.required" => "Username harus diisi",
                "username.unique" => "Username sudah digunakan",
                "phone_number.required" => "Nomor telepon harus diisi",
                "phone_number.unique" => "Nomor telepon sudah digunakan",
                "phone_number.digits_between" => "Nomor telepon harus memiliki panjang antara 10 hingga 15 karakter",
                "password.min" => "Password minimal 5 karakter",
                "is_active.required" => "Status harus diisi",
                "is_active.in" => "Status tidak sesuai",
                "level.required" => "Level harus diisi",
                "level.in" => "Level tidak valid",
                "debt_limit.required" => "Limit pihutang harus diisi",
                "debt_limit.integer" => "Limit pihutang tidak valid",
                "debt_limit.min" => "Limit pihutang tidak boleh minus",
                "image.image" => "Gambar yang di upload tidak valid",
                "image.max" => "Ukuran gambar maximal 1MB",
                "image.mimes" => "Format gambar harus giv/svg/jpeg/png/jpg",
                "province_id.required" => "Provinsi harus diisi",
                "province_id.integer" => "Provinsi tidak valid",
                "district_id.required" => "Kabupaten harus diisi",
                "district_id.integer" => "Kabupaten tidak valid",
                "sub_district_id.required" => "Kecamatan harus diisi",
                "sub_district_id.integer" => "Kecamatan tidak valid",
                "address.required" => "Alamat harus diisi",
                "description.required" => "Deskripsi harus diisi"
            ];

            $validator = Validator::make($request->all(), $rules, $messages);
            if ($validator->fails()) {
                return response()->json([
                    "status" => "error",
                    "message" => $validator->errors()->first(),
                ], 400);
            }

            $user = User::where('role', 'RESELLER')->where('id', $data['id'])->first();
            if (!$user) {
                return response()->json([
                    "status" => "error",
                    "message" => "Data tidak ditemukan"
                ], 404);
            }

            // cek provinsi
            $province = Province::find($request->province_id);
            if (!$province) {
                return response()->json([
                    "status" => "error",
                    "message" => "Provinsi tidak ditemukan",
                ], 400);
            }

            // cek kabupaten
            $district = District::find($request->district_id);
            if (!$district) {
                return response()->json([
                    "status" => "error",
                    "message" => "Kabupaten tidak ditemukan",
                ], 400);
            }

            // cek kecamatan
            $subDistrict = SubDistrict::find($request->sub_district_id);
            if (!$subDistrict) {
                return response()->json([
                    "status" => "error",
                    "message" => "Kecamatan tidak ditemukan",
                ], 400);
            }

            if ($data['password'] && $data['password'] != "") {
                $data['password'] = Hash::make($data['password']);
            } else {
                unset($data['password']);
            }

            // agar username tidak bisa diganti
            unset($data['username']);

            $data['phone_number'] = preg_replace('/^08/', '628', $data['phone_number']);

            // delete undefined data image
            unset($data["image"]);
            if ($request->file("image")) {
                $oldImagePath = "public/" . $user->image;
                if (Storage::exists($oldImagePath)) {
                    Storage::delete($oldImagePath);
                }
                $data["image"] = $request->file("image")->store("assets/user", "public");
            }

            $user->update($data);

            return response()->json([
                "status" => "success",
                "message" => "Berhasil menambahkan data pengguna"
            ]);
        } catch (\Exception $err) {
            if ($request->file("image")) {
                $uploadedImg = "public/assets/user" . $request->image->hashName();
                if (Storage::exists($uploadedImg)) {
                    Storage::delete($uploadedImg);
                }
            }
            return response()->json([
                "status" => "error",
                "message" => $err->getMessage()
            ], 500);
        }
    }


    public function softDelete(Request $request)
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
            $query = User::where("id", $id)->where("role", "RESELLER");
            $user = auth()->user();

            $property = $query->first();

            if (!$property) {
                return response()->json([
                    "status" => "error",
                    "message" => "Data tidak ditemukan"
                ], 404);
            }

            $property->delete();
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

    public function restore(Request $request)
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
            $query = User::withTrashed()->where("id", $id);

            $reseller = $query->first();

            if (!$reseller) {
                return response()->json([
                    "status" => "error",
                    "message" => "Data tidak ditemukan"
                ], 404);
            }

            $reseller->restore();

            return response()->json([
                "status" => "success",
                "message" => "Data berhasil direstore"
            ]);
        } catch (\Exception $err) {
            return response()->json([
                "status" => "error",
                "message" => $err->getMessage()
            ], 500);
        }
    }
}
