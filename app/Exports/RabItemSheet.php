<?php

namespace App\Exports;

use App\Models\RabItem;
use App\Models\User;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RabItemSheet implements FromQuery, WithHeadings, WithTitle, WithMapping, WithStyles
{
    private const PRIVILEGED_ROLES = ['bendahara_umum', 'ketua_yayasan', 'super_admin'];

    private int $rowNumber = 0;

    public function __construct(
        private array $filters,
        private User $user
    ) {
        Carbon::setLocale('id');
    }

    public function title(): string
    {
        return 'Item RAB';
    }

    public function headings(): array
    {
        return [
            'No',
            'Nomor RAB',
            'Divisi',
            'Kegiatan',
            'Catatan Kegiatan',
            'Biaya Anggaran',
            'Waktu Pelaksanaan',
        ];
    }

    public function query()
    {
        $filters  = $this->filters;
        $user     = $this->user;
        $roleName = $user->getRoleNames()->first();

        return RabItem::query()
            ->join('rabs', 'rab_items.rab_id', '=', 'rabs.id')
            ->whereHas('rab', function ($q) use ($filters, $roleName) {
                if ($roleName && str_starts_with($roleName, 'divisi_')) {
                    $q->where('divisi_id', $roleName);
                } elseif (in_array($roleName, self::PRIVILEGED_ROLES)) {
                    if (!empty($filters['divisi_id'])) {
                        $q->where('divisi_id', $filters['divisi_id']);
                    }
                }

                if (!empty($filters['tahun'])) {
                    $q->whereYear('bulan_pengajuan', $filters['tahun']);
                }

                if (!empty($filters['bulan'])) {
                    $q->whereMonth('bulan_pengajuan', $filters['bulan']);
                }
            })
            ->with('rab')
            ->orderBy('rabs.nomor_rab')
            ->orderBy('rab_items.id')
            ->select('rab_items.*');
    }

    public function map($item): array
    {
        return [
            ++$this->rowNumber,
            $item->rab->nomor_rab ?? '-',
            $item->rab->divisi_id ?? '-',
            $item->kegiatan,
            strip_tags($item->catatan_kegiatan ?? ''),
            (float) $item->biaya_anggaran,
            $item->waktu_pelaksanaan,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
