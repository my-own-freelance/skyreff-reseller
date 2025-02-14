<?php

namespace App\Exports;

use App\Models\TrxCommission;
use App\Models\TrxDebt;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Http\Request;

class TrxDebtExport implements FromCollection, WithHeadings, WithMapping

{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function collection()
    {
        $query = TrxDebt::with([
            "TrxProduct" => function ($query) {
                $query->select("id", "code");
            },
            "User" => function ($query) {
                $query->select("id", "code", "name");
            },
            "Bank" => function ($query) {
                $query->select("id", "title", "account");
            }
        ]);

        // filter reseller dari dashboard admin
        if ($this->request->query('user_id') && $this->request->query('user_id') != '') {
            $query->where('user_id', strtoupper($this->request->query('user_id')));
        }


        // filter type
        if ($this->request->query('type') && $this->request->query('type') != '') {
            $query->where('type', strtoupper($this->request->query('type')));
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

        return $query->orderBy('id', 'desc')
            ->get();
    }

    public function headings(): array
    {
        return [
            "Kode Trx",
            "Tipe",
            "Status",
            "Reseller",
            "Kode Reseller",
            "Nominal",
            "Hutang Awal",
            "Hutang Akhir",
            "Trx Ref",
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
            $trx->type == 'D' ? 'Hutang' : 'Bayar',
            $trx->status,
            $trx->User ? $trx->User->name : '',
            $trx->User ? $trx->User->code : '',
            $trx->amount,
            $trx->first_debt,
            $trx->last_debt,
            $trx->TrxProduct ? $trx->TrxProduct->code : "",
            $trx->Bank && $trx->type == "P" ? $trx->Bank->title . " (" . $trx->Bank->account . ")" : "",
            $trx->remark,
            Carbon::parse($trx->created_at)->addHours(7)->format('Y-m-d H:i:s'),
            Carbon::parse($trx->updated_at)->addHours(7)->format('Y-m-d H:i:s'),
        ];
    }
}
