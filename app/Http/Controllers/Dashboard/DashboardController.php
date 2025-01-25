<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Information;
use App\Models\TrxCommission;
use App\Models\TrxProduct;
use App\Models\User;
use App\Models\WebConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $title = "Dashboard Skyreff";
        $data = [];
        $user = Auth::user();
        $pageUrl = $user->role == "ADMIN" ? "pages.dashboard.admin.index" : "pages.dashboard.reseller.index";

        if ($user->role == "RESELLER") {
            $reseller = User::where("id", $user->id)->first();
            $wdCommisson = TrxCommission::where('user_id', $user->id)->whereIn('status', ['PENDING', 'PROCESS'])->sum('amount') ?? 0;
            $data = [
                "informations" => Information::where("is_active", "Y")->get(),
                "banners" => Banner::where("is_active", "Y")->get(),
                "trx_product" => TrxProduct::where("user_id", $user->id)->whereMonth('created_at', date('m'))->whereYear('created_at', date('Y'))->count(),
                "debt_limit" => 'Rp. ' . number_format($reseller->debt_limit, 0, ',', '.'),
                "debt_total" => 'Rp. ' . number_format($reseller->debt_total, 0, ',', '.'),
                "commission" => 'Rp. ' . number_format($reseller->commission, 0, ',', '.'),
                "wd_commission" => 'Rp. ' . number_format($wdCommisson, 0, ',', '.'),
            ];
        }
        return view($pageUrl, compact("title", "data"));
    }
}
