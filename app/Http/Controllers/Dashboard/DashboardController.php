<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\WebConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $title = "Dashboard Skyreff";
        $config = WebConfig::first();
        if ($config) {
            $title = $config->web_title;
        }

        $user = Auth::user();
        $pageUrl = $user->role == "ADMIN" ? "pages.dashboard.admin.index" : "pages.dashboard.reseller.index";
        return view($pageUrl, compact("title"));
    }
}
