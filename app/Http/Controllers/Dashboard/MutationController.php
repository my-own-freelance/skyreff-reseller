<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Mutation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MutationController extends Controller
{
    public function index()
    {
        $title = "Mutasi Komisi";
        $user = Auth::user();
        $reseller = [];
        $pageUrl = "pages.dashboard.reseller.mutation-commission";
        if ($user->role == "ADMIN") {
            $pageUrl = "pages.dashboard.admin.mutation-commission";
            $reseller = User::where("role", "RESELLER")->select("id", "name", "code")->orderBy("name", "asc")->get();
        }

        return view($pageUrl, compact("title", "reseller"));
    }

    // HANDLER API
    public function dataTable(Request $request)
    {
        $query = Mutation::with([
            "User" => function ($query) {
                $query->select("id", "name", "code");
            },
            "TrxProduct" => function ($query) {
                $query->select("id", "code");
            },
            "TrxCommission" => function ($query) {
                $query->select("id", "code");
            }
        ]);

        // filter by reseller ID
        $user = auth()->user();
        if ($user->role == "RESELLER") {
            $query->where('user_id', $user->id);
        }

        // filter code mutasi
        if ($request->query("search")) {
            $searchValue = $request->query("search")['value'];
            $query->where('code', 'like', '%' . $searchValue . '%');
        }

        // filter reseller dari dashboard admin
        if ($request->query('user_id') && $request->query('user_id') != '') {
            $query->where('user_id', strtoupper($request->query('user_id')));
        }

        // filter tipe
        if ($request->query("type") && $request->query('type') != "") {
            $query->where('type', $request->query('type'));
        }

        // filter tanggal awal - tanggal akhir per bulan saat ini
        $tglAwal = $request->query('tgl_awal');
        $tglAkhir = $request->query('tgl_akhir');

        if (!$tglAwal) {
            $tglAwal = Carbon::now('UTC')->startOfMonth()->subHour(7)->toDateTimeString(); // dikurangi 7 jam mengikuti waktu utc
        }

        if (!$tglAkhir) {
            $tglAkhir = Carbon::now('UTC')->endOfMonth()->subHour(7)->toDateTimeString(); // dikurangi 7 jam mengikuti waktu utc
        }

        if ($request->query('tgl_awal') && $request->query('tgl_akhir')) {
            $tglAwal = Carbon::createFromFormat('d/m/Y', $request->query('tgl_awal'), 'UTC')->startOfDay()->subHour(7)->toDateTimeString(); // dikurangi 7 jam mengikuti waktu utc
            $tglAkhir = Carbon::createFromFormat('d/m/Y', $request->query('tgl_akhir'), 'UTC')->endOfDay()->subHour(7)->toDateTimeString(); // dikurangi 7 jam mengikuti waktu utc
        }

        $query->whereBetween('created_at', [$tglAwal, $tglAkhir]);
        $recordsFiltered = $query->count();
        $data = $query->orderBy('id', 'desc')
            ->skip($request->query('start'))
            ->limit($request->query('length'))
            ->get();

        $user = auth()->user();
        $output = $data->map(function ($item) use ($user) {
            $item["amount"] = ($item["type"] == "C" || $item["type"] == "R") ? "<span class='text-success'>+ Rp. " . number_format($item->amount, 0, ',', '.') . "</span>" : "<span class='text-danger'>- Rp. " . number_format($item->amount, 0, ',', '.') . "</span>";
            $item["first_commission"] = "Rp. "  . number_format($item->first_commission, 0, ',', '.');
            $item["last_commission"] = "Rp. "  . number_format($item->last_commission, 0, ',', '.');
            $item['created'] = Carbon::parse($item->created_at)->addHours(7)->format('Y-m-d H:i:s');

            $item["ref_code"] = "";

            if ($item["type"] == "C" && $item["TrxProduct"]) {
                $item["ref_code"] = $item->TrxProduct->code;
            } else if (($item["type"] == "W" || $item['type'] == "R") && $item["TrxCommission"]) {
                $item["ref_code"] = $item->TrxCommission->code;
            }

            switch ($item["type"]) {
                case "C":
                    $item["type"] = "<span class='badge badge-success'>COMMISSION</span>";
                    break;
                case "R":
                    $item["type"] = "<span class='badge badge-secondary'>REFUND</span>";
                    break;
                case "W":
                    $item["type"] = "<span class='badge badge-info'>WITHDRAW</span>";
                    break;
                default:
                    $item["type"] = "<span class='badge badge-error'>UNKNOWN</span>";
                    break;
            }
            if ($user->role == "ADMIN") {
                $reseller = "<small> 
                                <strong>Nama</strong> :" . ($item->User ? $item->User->name : 'Reseller Deleted') .  "
                                <br>
                                <strong>Code</strong> :" . ($item->User ? $item->User->code : 'Reseller Deleted') . "
                                <br>
                            </small>";
                $item["reseller"] = $reseller;
            }

            unset($item["TrxProduct"]);
            unset($item["TrxCommission"]);
            unset($item["User"]);
            return $item;
        });

        $queryTotal = Mutation::whereBetween('created_at', [$tglAwal, $tglAkhir]);
        if ($user->role == "RESELLER") {
            $queryTotal->where('user_id', $user->id);
        }

        $total = $queryTotal->count();
        return response()->json([
            'draw' => $request->query('draw'),
            'recordsFiltered' => $recordsFiltered,
            'recordsTotal' => $total,
            'data' => $output,
            "res" => $data
        ]);
    }
}
