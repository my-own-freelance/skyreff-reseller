<?php

namespace App\Exports;

use App\Models\TrxTopup;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Http\Request;

class TrxTopupExport implements FromCollection,  WithHeadings, WithMapping
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function collection()
    {
        $query = TrxTopup::with([
            "User" => function ($query) {
                $query->select("id", "code", "name");
            },
            "Bank" => function ($query) {
                $query->select("id", "title", "account");
            }
        ]);

        // FILTER BY RESELLER ID
        $user = auth()->user();
        if ($user->role == "RESELLER") {
            $query->where('user_id', $user->id);
        }

        // filter code trx
        if ($this->request->query("search")) {
            $searchValue = $this->request->query("search")['value'];
            $query->where('code', 'like', '%' . $searchValue . '%');
        }

        // filter status
        if ($this->request->query('status') && $this->request->query('status') != '') {
            $query->where('status', strtoupper($this->request->query('status')));
        }

        // filter tanggal awal - tanggal akhir per bulan saat ini
        $tglAwal = $this->request->query('tgl_awal');
        $tglAkhir = $this->request->query('tgl_akhir');

        if (!$tglAwal) {
            $tglAwal = Carbon::now('UTC')->startOfMonth()->subHour(7)->toDateTimeString(); // dikurangi 7 jam mengikuti waktu utc
        }

        if (!$tglAkhir) {
            $tglAkhir = Carbon::now('UTC')->endOfMonth()->subHour(7)->toDateTimeString(); // dikurangi 7 jam mengikuti waktu utc
        }

        if ($this->request->query('tgl_awal') && $this->request->query('tgl_akhir')) {
            $tglAwal = Carbon::createFromFormat('d/m/Y', $this->request->query('tgl_awal'), 'UTC')->startOfDay()->subHour(7)->toDateTimeString(); // dikurangi 7 jam mengikuti waktu utc
            $tglAkhir = Carbon::createFromFormat('d/m/Y', $this->request->query('tgl_akhir'), 'UTC')->endOfDay()->subHour(7)->toDateTimeString(); // dikurangi 7 jam mengikuti waktu utc
        }

        $query->whereBetween('created_at', [$tglAwal, $tglAkhir]);
        return $query->orderBy('id', 'desc')->get();
    }

    public function headings(): array
    {
        return [
            "Kode Trx",
            "Status",
            "Reseller",
            "Kode Reseller",
            "Nominal",
            "Bank Bayar",
            "Catatan",
            "Tgl Request",
            "Tgl Update"
        ];
    }

    public function map($trx): array
    {
        return [
            $trx->code,
            $trx->status,
            $trx->User ? $trx->User->name : '',
            $trx->User ? $trx->User->code : '',
            $trx->amount,
            $trx->Bank ? $trx->Bank->title . " (" . $trx->Bank->account . ")" : "",
            $trx->remark,
            Carbon::parse($trx->created_at)->addHours(7)->format('Y-m-d H:i:s'),
            Carbon::parse($trx->updated_at)->addHours(7)->format('Y-m-d H:i:s'),
        ];
    }
}
