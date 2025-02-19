<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ExternalPacketController extends Controller
{
    public function xlotp()
    {
        $title = "XL AXIS OTP Solution";
        return view("pages.dashboard.reseller.xlotp", compact("title"));
    }
}
