<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index()
    {
        $title = "Master Produk";
        $categories = ProductCategory::all();
        return view("pages.dashboard.admin.product", compact("title", "categories"));
    }

    // HANDLER API
    public function dataTable(Request $request)
    {
        $query = Product::with(["ProductCategory" => function ($query) {
            $query->select("id", "title");
        }]);

        if ($request->query("search")) {
            $searchValue = $request->query("search")['value'];
            $query->where(function ($query) use ($searchValue) {
                $query->where('title', 'like', '%' . $searchValue . '%')
                    ->Orwhere('code', 'like', '%' . $searchValue . '%')
                    ->Orwhere('excerpt', 'like', '%' . $searchValue . '%');
            });
        }

        // filter kategori
        if ($request->query("product_category_id") && $request->query('product_category_id') != "") {
            $query->where('product_category_id', $request->query('product_category_id'));
        }

        // filter status
        if ($request->query("is_active") && $request->query('is_active') != "") {
            $query->where('is_active', $request->query('is_active'));
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
                                <a class='dropdown-item' onclick='return addGallery(\"{$item->id}\");' href='javascript:void(0)' title='Tambah Gallery'>Tambah Gallery</a>                                <a class='dropdown-item' onclick='return removeData(\"{$item->id}\");' href='javascript:void(0)' title='Hapus'>Hapus</a>
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
            $title = "<small> 
                        <strong>Judul</strong> :" . Str::limit(strip_tags($item->title), 100) .  "
                        <br>
                        <strong>Code</strong> :" . $item->code . "
                        <br>
                    </small>";
            $excerpt = "<p>" . Str::limit(strip_tags($item->excerpt), 150) . "</p>";
            $price = '<small>
                        <strong>Harga Beli</strong> : Rp. ' . number_format($item->purchase_price, 0, ',', '.') . '
                        <br>
                        <strong>Harga Jual</strong> : Rp. ' . number_format($item->selling_price, 0, ',', '.') . '
                        <br>
                    </small>';
            $commission = '<small>
                        <strong>Regular</strong> : Rp. ' . number_format($item->commission_regular, 0, ',', '.') . '
                        <br>
                        <strong>VIP</strong> : Rp. ' . number_format($item->commission_vip, 0, ',', '.') . '
                        <br>
                    </small>';
            $item['action'] = $action;
            $item['is_active'] = $is_active;
            $item['image'] = $image;
            $item['title'] = $title;
            $item['excerpt'] = $excerpt;
            $item['price'] = $price;
            $item['commission'] = $commission;
            $item['category'] = $item->ProductCategory->title;

            unset($item['description']);
            unset($item['purchase_price']);
            unset($item['selling_price']);
            unset($item['commission_regular']);
            unset($item['commission_vip']);
            unset($item['ProductCategory']);
            return $item;
        });

        $total = Product::count();
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
            $product = Product::find($id);

            if (!$product) {
                return response()->json([
                    "status" => "error",
                    "message" => "Data tidak ditemukan",
                ], 404);
            }

            return response()->json([
                "status" => "success",
                "data" => $product
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
                "purchase_price" => "required|integer|min:1",
                "selling_price" => "required|integer|min:1",
                "commission_regular" => "integer",
                "commission_vip" => "integer",
                "is_active" => "required|string|in:Y,N",
                "stock" => "required|integer|min:1",
                "image" => "required|image|max:2048|mimes:giv,svg,jpeg,png,jpg",
                "excerpt" => "required|string|max:250",
                "description" => "required|string",
                "product_category_id" => "required|integer"
            ];

            $messages = [
                "title.required" => "Judul harus diisi",
                "purchase_price.required" => "Harga beli harus diisi",
                "purchase_price.integer" => "Harga beli tidak valid",
                "purchase_price.min" => "Harga beli minimal Rp.1",
                "selling_price.required" => "Harga jual harus diisi",
                "selling_price.integer" => "Harga jual tidak valid",
                "selling_price.min" => "Harga jual minimal Rp.1",
                "commission_regular.integer" => "Komisi Regular tidak valid",
                "commission_vip.integer" => "Komisi VIP tidak valid",
                "is_active.required" => "Status harus diisi",
                "is_active.in" => "Status tidak sesuai",
                "stock.required" => "Stock harus diisi",
                "stock.integer" => "Stock tidak valid",
                "stock.min" => "Stock minimal 1",
                "image.required" => "Gambar harus di isi",
                "image.image" => "Gambar yang di upload tidak valid",
                "image.max" => "Ukuran gambar maximal 2MB",
                "image.mimes" => "Format gambar harus giv/svg/jpeg/png/jpg",
                "excerpt.required" => "Kutipan harus diisi",
                "excerpt.max" => "Kutipan harus kurang dari 250 karakter",
                "description.required" => "Deskripsi harus diisi",
                "product_category_id.required" => "Kategori Produk harus diisi",
                "product_category_id.integer" => "Kategori Produk tidak valid",
            ];

            $validator = Validator::make($data, $rules, $messages);
            if ($validator->fails()) {
                return response()->json([
                    "status" => "error",
                    "message" => $validator->errors()->first(),
                ], 400);
            }

            if ($request->file('image')) {
                $data['image'] = $request->file('image')->store('assets/product', 'public');
            }
            unset($data['id']);
            $data["code"] = strtoupper(Str::random(10));

            // jika code nya di custom
            if ($request->code && $request->code != "") {
                $existingByCode = Product::where("code", $request->code)->first();
                if ($existingByCode) {
                    return response()->json([
                        "status" => "error",
                        "message" => "Code product sudah digunakan",
                    ], 400);
                }

                // else
                $data["code"] = $request->code;
            }

            // cek data category
            $productCategory = ProductCategory::find($request->product_category_id);
            if (!$productCategory) {
                return response()->json([
                    "status" => "error",
                    "message" => "Data Category tidak ditemukan",
                ], 400);
            }

            Product::create($data);
            return response()->json([
                "status" => "success",
                "message" => "Data berhasil dibuat"
            ]);
        } catch (\Exception $err) {
            if ($request->file("image")) {
                $uploadedImg = "public/assets/product" . $request->image->hashName();
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
                "purchase_price" => "required|integer|min:1",
                "selling_price" => "required|integer|min:1",
                "commission_regular" => "integer",
                "commission_vip" => "integer",
                "is_active" => "required|string|in:Y,N",
                "stock" => "required|integer|min:0",
                "image" => "nullable",
                "excerpt" => "required|string|max:250",
                "description" => "required|string",
                "product_category_id" => "required|integer"
            ];

            if ($request->file('image')) {
                $rules['image'] .= '|image|max:2048|mimes:giv,svg,jpeg,png,jpg';
            }

            $messages = [
                "title.required" => "Judul harus diisi",
                "purchase_price.required" => "Harga beli harus diisi",
                "purchase_price.integer" => "Harga beli tidak valid",
                "purchase_price.min" => "Harga beli minimal Rp.1",
                "selling_price.required" => "Harga jual harus diisi",
                "selling_price.integer" => "Harga jual tidak valid",
                "selling_price.min" => "Harga jual minimal Rp.1",
                "commission_regular.integer" => "Komisi Regular tidak valid",
                "commission_vip.integer" => "Komisi VIP tidak valid",
                "is_active.required" => "Status harus diisi",
                "is_active.in" => "Status tidak sesuai",
                "stock.required" => "Stock harus diisi",
                "stock.integer" => "Stock tidak valid",
                "stock.min" => "Stock tidak boleh minus",
                "image.image" => "Gambar yang di upload tidak valid",
                "image.max" => "Ukuran gambar maximal 2MB",
                "image.mimes" => "Format gambar harus giv/svg/jpeg/png/jpg",
                "excerpt.required" => "Kutipan harus diisi",
                "excerpt.max" => "Kutipan harus kurang dari 250 karakter",
                "description.required" => "Deskripsi harus diisi",
                "product_category_id.required" => "Kategori Produk harus diisi",
                "product_category_id.integer" => "Kategori Produk tidak valid",
            ];

            $validator = Validator::make($data, $rules, $messages);
            if ($validator->fails()) {
                return response()->json([
                    "status" => "error",
                    "message" => $validator->errors()->first(),
                ], 400);
            }

            $product = Product::find($data['id']);
            if (!$product) {
                return response()->json([
                    "status" => "error",
                    "message" => "Data tidak ditemukan"
                ], 404);
            }

            // delete undefined data image
            unset($data["image"]);
            if ($request->file("image")) {
                $oldImagePath = "public/" . $product->image;
                if (Storage::exists($oldImagePath)) {
                    Storage::delete($oldImagePath);
                }
                $data["image"] = $request->file("image")->store("assets/product", "public");
            }

            // jika code nya di custom
            if ($request->code && $request->code != "" && $request->code != $product->code) {
                $existingByCode = Product::where("code", $request->code)->first();
                if ($existingByCode) {
                    return response()->json([
                        "status" => "error",
                        "message" => "Code product sudah digunakan",
                    ], 400);
                }

                // else
                $data["code"] = $request->code;
            }

            // cek data category
            $productCategory = ProductCategory::find($request->product_category_id);
            if (!$productCategory) {
                return response()->json([
                    "status" => "error",
                    "message" => "Data Category tidak ditemukan",
                ], 400);
            }


            $product->update($data);
            return response()->json([
                "status" => "success",
                "message" => "Data berhasil diperbarui"
            ]);
        } catch (\Exception $err) {
            if ($request->file("image")) {
                $uploadedImg = "public/assets/product" . $request->image->hashName();
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

            $product = Product::find($data['id']);
            if (!$product) {
                return response()->json([
                    "status" => "error",
                    "message" => "Data tidak ditemukan"
                ], 404);
            }
            $product->update($data);
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
            $product = Product::find($id);
            if (!$product) {
                return response()->json([
                    "status" => "error",
                    "message" => "Data tidak ditemukan"
                ], 404);
            }
            $oldImagePath = "public/" . $product->image;
            if (Storage::exists($oldImagePath)) {
                Storage::delete($oldImagePath);
            }

            $product->delete();
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
