<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\WebConfig;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $title = "Dashboard Skyreff";
        $config = WebConfig::first();
        if ($config) {
            $title = $config->web_title;
        }

        return view("pages.dashboard.index", compact("title"));
    }
}
