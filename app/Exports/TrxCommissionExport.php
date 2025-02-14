<?php

namespace App\Exports;

use App\Models\TrxCommission;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Http\Request;


class TrxCommissionExport implements FromCollection,  WithHeadings, WithMapping
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function collection()
    {
        $query = TrxCommission::with([
            "User" => function ($query) {
                $query->select("id", "code", "name");
            }
        ]);

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
        return $query->orderBy('id', 'desc')
            ->get();
    }

    public function headings(): array
    {
        return [
            "Kode Trx",
            "Status",
            "Reseller",
            "Kode Reseller",
            "Bank",
            "Rekening",
            "Nominal",
            "Admin",
            "Total",
            "Tgl Trx",
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
            $trx->bank_name,
            $trx->bank_account,
            $trx->amount,
            $trx->admin,
            $trx->total_amount,
            Carbon::parse($trx->created_at)->addHours(7)->format('Y-m-d H:i:s'),
            Carbon::parse($trx->updated_at)->addHours(7)->format('Y-m-d H:i:s'),
        ];
    }
}
