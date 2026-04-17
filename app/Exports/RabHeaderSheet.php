<?php

namespace App\Exports;

use App\Models\Rab;
use App\Models\User;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RabHeaderSheet implements FromQuery, WithHeadings, WithTitle, WithMapping, WithStyles
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
        return 'Data RAB';
    }

    public function headings(): array
    {
        return [
            'No',
            'Nomor RAB',
            'Divisi',
            'Bulan Pengajuan',
            'Total Kegiatan',
            'Total Anggaran',
            'Status',
            'Dibuat Oleh',
            'Tanggal Dibuat',
        ];
    }

    public function query()
    {
        $query = Rab::query()->with('dibuatOleh');

        $roleName = $this->user->getRoleNames()->first();

        if ($roleName && str_starts_with($roleName, 'divisi_')) {
            $query->where('divisi_id', $roleName);
        } elseif (in_array($roleName, self::PRIVILEGED_ROLES)) {
            if (!empty($this->filters['divisi_id'])) {
                $query->where('divisi_id', $this->filters['divisi_id']);
            }
        }

        if (!empty($this->filters['tahun'])) {
            $query->whereYear('bulan_pengajuan', $this->filters['tahun']);
        }

        if (!empty($this->filters['bulan'])) {
            $query->whereMonth('bulan_pengajuan', $this->filters['bulan']);
        }

        return $query->orderBy('nomor_rab');
    }

    public function map($rab): array
    {
        return [
            ++$this->rowNumber,
            $rab->nomor_rab,
            $rab->divisi_id,
            $rab->bulan_pengajuan->translatedFormat('F Y'),
            $rab->total_kegiatan,
            (float) $rab->total_biaya_anggaran,
            $rab->status_label,
            $rab->dibuatOleh->name ?? '-',
            $rab->created_at->format('d/m/Y'),
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
