<?php

namespace App\Exports;

use App\Models\PurchaseRequestItem;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PurchaseRequestItemSheet implements FromQuery, WithHeadings, WithMapping, WithTitle, WithColumnFormatting, WithStyles
{
    private int $rowNumber = 0;

    public function __construct(
        private $query
    ) {}

    public function title(): string
    {
        return 'Item Barang';
    }

    public function headings(): array
    {
        return [
            'No',
            'Nomor Pengajuan',
            'Divisi',
            'Nama Barang',
            'Satuan',
            'Jumlah',
            'Biaya Estimasi',
            'Sudah Dibeli',
            'Harga Aktual',
        ];
    }

    public function query()
    {
        return PurchaseRequestItem::query()
            ->join('purchase_requests', 'purchase_request_items.purchase_request_id', '=', 'purchase_requests.id')
            ->whereIn('purchase_requests.id', $this->query->clone()->select('id'))
            ->with('purchaseRequest')
            ->orderBy('purchase_requests.nomor_pengajuan', 'asc')
            ->orderBy('purchase_request_items.id', 'asc')
            ->select('purchase_request_items.*');
    }

    public function map($item): array
    {
        return [
            ++$this->rowNumber,
            $item->purchaseRequest->nomor_pengajuan ?? '-',
            $item->purchaseRequest->divisi_id ?? '-',
            $item->nama_barang,
            $item->satuan,
            $item->jumlah,
            (float) $item->biaya_estimasi,
            $item->sudah_dibeli ? 'Ya' : 'Tidak',
            $item->harga_aktual !== null ? (float) $item->harga_aktual : '',
        ];
    }

    public function columnFormats(): array
    {
        return [
            'G' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'I' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
