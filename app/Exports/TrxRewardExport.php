<?php

namespace App\Exports;

use App\Models\TrxReward;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Http\Request;

class TrxRewardExport implements FromCollection, WithHeadings, WithMapping
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function collection()
    {
        $query = TrxReward::with([
            "User" => function ($query) {
                $query->select("id", "code", "name");
            },
            "Reward" => function ($query) {
                $query->select("id", "title");
            }
        ])->whereNot("reward_id", null);


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
            "Status",
            "Reward",
            "Reseller",
            "Kode Reseller",
            "Waktu Claim",
        ];
    }

    public function map($trx): array
    {
        return [
            $trx->status,
            $trx->Reward ? $trx->Reward->title : '',
            $trx->User ? $trx->User->name : '',
            $trx->User ? $trx->User->code : '',
            Carbon::parse($trx->created_at)->addHours(7)->format('Y-m-d H:i:s'),
        ];
    }
}
