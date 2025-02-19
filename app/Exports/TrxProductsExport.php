<?php

namespace App\Exports;

use App\Models\TrxProduct;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Http\Request;

class TrxProductsExport implements FromCollection, WithHeadings, WithMapping
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function collection()
    {
        $query = TrxProduct::with([
            "Product" => function ($query) {
                $query->select("id", "title", "code", "product_category_id")
                    ->with("ProductCategory:id,title");
            },
            "User" => function ($query) {
                $query->select("id", "code", "name");
            },
            "Bank" => function ($query) {
                $query->select("id", "title", "account");
            },
        ]);

        // Filter kode transaksi
        if ($this->request->query("search")) {
            $searchValue = $this->request->query("search")['value'];
            $query->where('code', 'like', '%' . $searchValue . '%');
        }

        // Filter status
        if ($this->request->query('status') && $this->request->query('status') != '') {
            $query->where('status', strtoupper($this->request->query('status')));
        }

        // Filter tipe pembayaran
        if ($this->request->query('payment_type') && $this->request->query('payment_type') != '') {
            $query->where("payment_type", strtoupper($this->request->query("payment_type")));
        }

        // Filter kategori produk
        if ($this->request->query('product_category_id') && $this->request->query('product_category_id') != "") {
            $productCategoryId = $this->request->query('product_category_id');
            $query->whereHas('product.productCategory', function ($query) use ($productCategoryId) {
                $query->where('id', $productCategoryId);
            });
        }

        // Filter tanggal awal - akhir
        $tglAwal = $this->request->query('tgl_awal') ?
            Carbon::createFromFormat('d/m/Y', $this->request->query('tgl_awal'), 'UTC')->startOfDay()->subHour(7)->toDateTimeString() :
            Carbon::now('UTC')->startOfMonth()->subHour(7)->toDateTimeString();

        $tglAkhir = $this->request->query('tgl_akhir') ?
            Carbon::createFromFormat('d/m/Y', $this->request->query('tgl_akhir'), 'UTC')->endOfDay()->subHour(7)->toDateTimeString() :
            Carbon::now('UTC')->endOfMonth()->subHour(7)->toDateTimeString();

        $query->whereBetween('created_at', [$tglAwal, $tglAkhir]);

        return  $query->orderBy('id', 'desc')
            ->get();
    }

    public function headings(): array
    {
        return [
            "Kode Trx",
            "Status",
            "Reseller",
            "Kode Reseller",
            "Produk",
            "Kode Produk",
            "Kategori Produk",
            "Harga",
            "Quantity",
            "Total Bayar",
            "Komisi",
            "Profit",
            "Tipe Bayar",
            "Bank Tujuan",
            "Catatan",
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
            $trx->Product ? $trx->Product->title : '',
            $trx->Product ? $trx->Product->code : '',
            $trx->Product ? ($trx->Product->ProductCategory ? $trx->Product->ProductCategory->title : '') : '',
            $trx->amount,
            $trx->qty,
            $trx->total_amount,
            $trx->commission,
            $trx->profit,
            $trx->payment_type == 'TRANSFER' ? 'TF BANK' : ($trx->payment_type == 'BALANCE' ? 'SALDO' : 'Hutang'),
            $trx->Bank ? $trx->Bank->title . " (" . $trx->Bank->account . ")" : "",
            $trx->notes ?? "",
            Carbon::parse($trx->created_at)->addHours(7)->format('Y-m-d H:i:s'),
            Carbon::parse($trx->updated_at)->addHours(7)->format('Y-m-d H:i:s'),
        ];
    }
}
