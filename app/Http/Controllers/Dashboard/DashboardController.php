<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Information;
use App\Models\Mutation;
use App\Models\Product;
use App\Models\TrxCommission;
use App\Models\TrxCompensation;
use App\Models\TrxProduct;
use App\Models\User;
use App\Models\WebConfig;
use Carbon\Carbon;
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

            $tglAwal = Carbon::now('UTC')->startOfMonth()->subHour(7)->toDateTimeString(); // dikurangi 7 jam mengikuti waktu utc
            $tglAkhir = Carbon::now('UTC')->endOfMonth()->subHour(7)->toDateTimeString(); // dikurangi 7 jam mengikuti waktu utc
            $commissionThisMonth = Mutation::where("type", "C")->where("user_id", $reseller->id)->whereBetween("created_at", [$tglAwal, $tglAkhir])->sum("amount") ?? 0;
            $data = [
                "informations" => Information::where("is_active", "Y")->get(),
                "banners" => Banner::where("is_active", "Y")->get(),
                "trx_product" => TrxProduct::where("user_id", $user->id)->whereBetween("created_at", [$tglAwal, $tglAkhir])->count(),
                "debt_limit" => 'Rp. ' . number_format($reseller->debt_limit, 0, ',', '.'),
                "total_debt" => 'Rp. ' . number_format($reseller->total_debt, 0, ',', '.'),
                "commission" => 'Rp. ' . number_format($reseller->commission, 0, ',', '.'),
                "balance" => 'Rp. ' . number_format($reseller->balance, 0, ',', '.'),
                "wd_commission" => 'Rp. ' . number_format($wdCommisson, 0, ',', '.'),
                "month_commission" => 'Rp. ' . number_format($commissionThisMonth, 0, ',', '.'),
                "level" => $reseller->level,
                "reseller_id" => $reseller->id
            ];
        } else {
            $totalAmount = 0;
            $transfer = 0;
            $balance = 0;
            $debt = 0;
            $profit = 0;
            $commission = 0;

            $tglAwal = Carbon::now('UTC')->startOfMonth()->subHour(7)->toDateTimeString(); // dikurangi 7 jam mengikuti waktu utc
            $tglAkhir = Carbon::now('UTC')->endOfMonth()->subHour(7)->toDateTimeString(); // dikurangi 7 jam mengikuti waktu utc
            $trxProduct = TrxProduct::where("status", "SUCCESS")->whereBetween("created_at", [$tglAwal, $tglAkhir])->get();
            foreach ($trxProduct as $trx) {
                $totalAmount += $trx->total_amount;
                $commission += $trx->commission;
                $profit += $trx->profit;
                if ($trx->payment_type == "TRANSFER") {
                    $transfer += $trx->total_amount;
                } else if ($trx->payment_type == "DEBT") {
                    $debt += $trx->total_amount;
                } else {
                    $balance += $trx->total_amount;
                }
            }

            $reqWd = TrxCommission::where('status', 'PENDING')->sum('amount') ?? 0;
            $reseller = User::where("role", "RESELLER")
                ->selectRaw("SUM(balance) as total_balance, SUM(total_debt) as total_debt, SUM(commission) as total_commission")
                ->first();

            $data = [
                "trx_total_amount" => 'Rp. ' . number_format($totalAmount, 0, ',', '.'),
                "trx_transfer" => 'Rp. ' . number_format($transfer, 0, ',', '.'),
                "trx_balance" => 'Rp. ' . number_format($balance, 0, ',', '.'),
                "trx_debt" => 'Rp. ' . number_format($debt, 0, ',', '.'),
                "trx_profit" => 'Rp. ' . number_format($profit, 0, ',', '.'),
                "trx_commission" => 'Rp. ' . number_format($commission, 0, ',', '.'),
                "req_wd" => 'Rp. ' . number_format($reqWd, 0, ',', '.'),
                "debt_all_reseller" => 'Rp. ' . number_format($reseller->total_debt, 0, ',', '.'),
                "commission_all_reseller" => 'Rp. ' . number_format($reseller->total_commission, 0, ',', '.'),
                "balance_all_reseller" => 'Rp. ' . number_format($reseller->total_balance, 0, ',', '.'),
                "total_product" => Product::count(),
                "total_trx" => TrxProduct::whereBetween("created_at", [$tglAwal, $tglAkhir])->count(),
                "total_reseller" => User::where("role", "RESELLER")->count(),
                "total_compensation" => TrxCompensation::where("status", "PENDING")->count()
            ];
        }
        return view($pageUrl, compact("title", "data"));
    }

    public function getStatikSession()
    {
        try {
            $user = User::find(auth()->user()->id);
            if (!$user) {
                return response()->json([
                    "status" => "error",
                    "message" => "User session not found !"
                ]);
            }

            return response()->json([
                "status" => "success",
                "data" => $user
            ]);
        } catch (\Throwable $err) {
            return response()->json([
                "status" => "error",
                "message" => $err->getMessage(),
            ], 500);
        }
    }

    public function getStatisticChart()
    {
        try {
            $year = Carbon::now()->year;

            // Inisialisasi array bulan dari Januari sampai Desember
            $months = range(1, 12);

            // Ambil data berdasarkan bulan
            $statistics = TrxProduct::selectRaw('
                    MONTH(created_at) as month,
                    SUM(total_amount) as total_sales,
                    SUM(CASE WHEN payment_type = "BALANCE" THEN total_amount ELSE 0 END) as balance_sales,
                    SUM(CASE WHEN payment_type = "TRANSFER" THEN total_amount ELSE 0 END) as transfer_sales,
                    SUM(CASE WHEN payment_type = "DEBT" THEN total_amount ELSE 0 END) as debt_sales,
                    SUM(profit) as total_profit
                ')
                ->where('status', 'SUCCESS')
                ->whereYear('created_at', $year)
                ->groupBy('month')
                ->orderBy('month')
                ->get()
                ->keyBy('month'); // Mengubah hasil query menjadi associative array berdasarkan bulan

            // Menyusun hasil agar tetap mencakup semua bulan, meskipun tidak ada transaksi
            $totalSalesData = [];
            $transferSalesData = [];
            $balanceSalesData = [];
            $debtSalesData = [];
            $profitData = [];

            foreach ($months as $month) {
                $totalSalesData[] = $statistics[$month]->total_sales ?? 0;
                $transferSalesData[] = $statistics[$month]->transfer_sales ?? 0;
                $balanceSalesData[] = $statistics[$month]->balance_sales ?? 0;
                $debtSalesData[] = $statistics[$month]->debt_sales ?? 0;
                $profitData[] = $statistics[$month]->total_profit ?? 0;
            }

            // Struktur data output sesuai dengan yang diminta
            $data = [
                [
                    "label" => "Total Penjualan",
                    "borderColor" => "#177dff",
                    "pointBackgroundColor" => "rgba(23, 125, 255, 0.6)",
                    "pointRadius" => 0,
                    "backgroundColor" => "rgba(23, 125, 255, 0.4)",
                    "legendColor" => "#177dff",
                    "fill" => true,
                    "borderWidth" => 2,
                    "data" => $totalSalesData
                ],
                [
                    "label" => "Transfer",
                    "borderColor" => "#fdaf4b",
                    "pointBackgroundColor" => "rgba(253, 175, 75, 0.6)",
                    "pointRadius" => 0,
                    "backgroundColor" => "rgba(253, 175, 75, 0.4)",
                    "legendColor" => "#fdaf4b",
                    "fill" => true,
                    "borderWidth" => 2,
                    "data" => $transferSalesData
                ],
                [
                    "label" => "Saldo",
                    "borderColor" => "#6610f2",
                    "pointBackgroundColor" => "rgba(102, 16, 242, 0.6)",
                    "pointRadius" => 0,
                    "backgroundColor" => "rgba(102, 16, 242, 0.4)",
                    "legendColor" => "#6610f2",
                    "fill" => true,
                    "borderWidth" => 2,
                    "data" => $balanceSalesData
                ],                
                [
                    "label" => "Hutang",
                    "borderColor" => "#f3545d",
                    "pointBackgroundColor" => "rgba(243, 84, 93, 0.6)",
                    "pointRadius" => 0,
                    "backgroundColor" => "rgba(243, 84, 93, 0.4)",
                    "legendColor" => "#f3545d",
                    "fill" => true,
                    "borderWidth" => 2,
                    "data" => $debtSalesData
                ],
                [
                    "label" => "Profit",
                    "borderColor" => "#28a745",
                    "pointBackgroundColor" => "rgba(40, 167, 69, 0.6)",
                    "pointRadius" => 0,
                    "backgroundColor" => "rgba(40, 167, 69, 0.4)",
                    "legendColor" => "#28a745",
                    "fill" => true,
                    "borderWidth" => 2,
                    "data" => $profitData
                ],
                // "all data "=> $statistics
            ];

            return response()->json([
                "status" => "success",
                "data" => $data
            ]);
        } catch (\Throwable $err) {
            return response()->json([
                "status" => "error",
                "message" => $err->getMessage(),
                "data" => [
                    [
                        "label" => "Total Penjualan",
                        "borderColor" => "#177dff",
                        "pointBackgroundColor" => "rgba(23, 125, 255, 0.6)",
                        "pointRadius" => 0,
                        "backgroundColor" => "rgba(23, 125, 255, 0.4)",
                        "legendColor" => "#177dff",
                        "fill" => true,
                        "borderWidth" => 2,
                        "data" => [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0]
                    ],
                    [
                        "label" => "Transfer",
                        "borderColor" => "#fdaf4b",
                        "pointBackgroundColor" => "rgba(253, 175, 75, 0.6)",
                        "pointRadius" => 0,
                        "backgroundColor" => "rgba(253, 175, 75, 0.4)",
                        "legendColor" => "#fdaf4b",
                        "fill" => true,
                        "borderWidth" => 2,
                        "data" => [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0]
                    ],
                    [
                        "label" => "Hutang",
                        "borderColor" => "#f3545d",
                        "pointBackgroundColor" => "rgba(243, 84, 93, 0.6)",
                        "pointRadius" => 0,
                        "backgroundColor" => "rgba(243, 84, 93, 0.4)",
                        "legendColor" => "#f3545d",
                        "fill" => true,
                        "borderWidth" => 2,
                        "data" => [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0]
                    ],
                    [
                        "label" => "Profit",
                        "borderColor" => "#28a745",
                        "pointBackgroundColor" => "rgba(40, 167, 69, 0.6)",
                        "pointRadius" => 0,
                        "backgroundColor" => "rgba(40, 167, 69, 0.4)",
                        "legendColor" => "#28a745",
                        "fill" => true,
                        "borderWidth" => 2,
                        "data" => [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0]
                    ]
                ]
            ], 500);
        }
    }
}
