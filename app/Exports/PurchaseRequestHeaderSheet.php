<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PurchaseRequestHeaderSheet implements FromQuery, WithHeadings, WithMapping, WithTitle, WithColumnFormatting, WithStyles
{
    private int $rowNumber = 0;

    public function __construct(
        private $query
    ) {}

    public function title(): string
    {
        return 'Data Pengajuan';
    }

    public function headings(): array
    {
        return [
            'No',
            'Nomor Pengajuan',
            'Divisi',
            'Judul Pengajuan',
            'Tanggal Dibutuhkan',
            'Total Item',
            'Total Biaya Estimasi',
            'Total Biaya Aktual',
            'Status',
            'Dibuat Oleh',
            'Tanggal Dibuat',
        ];
    }

    public function query()
    {
        return $this->query->clone()
            ->with('dibuatOleh')
            ->orderBy('nomor_pengajuan', 'asc');
    }

    public function map($pr): array
    {
        return [
            ++$this->rowNumber,
            $pr->nomor_pengajuan,
            $pr->divisi_id,
            $pr->judul_pengajuan,
            $pr->tanggal_dibutuhkan?->format('d/m/Y'),
            $pr->total_item,
            (float) $pr->total_biaya_estimasi,
            (float) $pr->total_biaya_aktual,
            $pr->status_label,
            $pr->dibuatOleh->name ?? '-',
            $pr->created_at->format('d/m/Y'),
        ];
    }

    public function columnFormats(): array
    {
        return [
            'G' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'H' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
