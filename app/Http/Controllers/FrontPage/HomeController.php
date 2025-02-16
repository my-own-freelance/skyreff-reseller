<?php

namespace App\Http\Controllers\FrontPage;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class HomeController extends Controller
{
    public function index()
    {
        $banners = Banner::where("is_active", 'Y')->limit(8)->get();
        $products = Product::where("is_active", "Y")
            ->whereHas("ProductCategory", function ($query) {
                $query->where("is_active", "Y");
            })->with(["ProductCategory" => function ($query) {
                $query->select("id", "title", "image");
            }])->orderBy('created_at', 'desc')->simplePaginate(20);
        return view("pages.frontpage.index", compact('banners', 'products'));
    }
}
