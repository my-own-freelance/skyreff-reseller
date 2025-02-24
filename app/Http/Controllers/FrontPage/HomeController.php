<?php

namespace App\Http\Controllers\FrontPage;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $banners = Banner::where("is_active", 'Y')->limit(8)->get();
        $categories = ProductCategory::where("is_active", "Y")->orderBy('title', 'asc')->get();
        $query = Product::query();

        if ($request->query("search") && $request->query('search') != "") {
            $searchValue = $request->query("search");
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

        $products = $query->where("is_active", "Y")
            ->whereHas("ProductCategory", function ($query) {
                $query->where("is_active", "Y");
            })->with(["ProductCategory" => function ($query) {
                $query->select("id", "title", "image");
            }])->orderBy('created_at', 'desc')->simplePaginate(20);
        return view("pages.frontpage.index", compact('banners', 'categories', 'products'));
    }
}
