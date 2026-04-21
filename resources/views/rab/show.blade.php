@extends('layouts.main')

@section('content')
    <!-- Container -->
    <div class="container-fixed">
        <div class="flex flex-wrap items-center lg:items-end justify-between gap-5 pb-7.5">
            <div class="flex flex-col justify-center gap-2">
                <h1 class="text-xl font-bold leading-none text-gray-900">
                    {{ $data['pageTitle'] }}
                </h1>
            </div>
            <div class="flex items-center gap-2.5">
                <a class="btn text-center btn-sm btn-primary" href="{{ route('rab.index') }}">
                    <i class="ki-filled ki-left"></i>Kembali
                </a>
                @can('updatePolicy', $data['rab'])
                    @if($data['rab']->isEditable())
                        <a class="btn btn-sm text-center btn-warning" href="{{ route('rab.edit', $data['rab']->id) }}">
                            <i class="ki-filled ki-notepad-edit"></i>Edit RAB
                        </a>
                    @endif
                @endcan
            </div>
        </div>
    </div>

    @include('partials.attention')

    <div class="container-fixed">
        <div class="grid gap-5 mx-auto">

            <!-- Header Card -->
            <div class="card pb-2.5">
                <div class="card-header">
                    <h3 class="card-title">Informasi RAB</h3>
                </div>
                <div class="card-body grid gap-4">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                            <label class="form-label max-w-56">Nomor RAB</label>
                            <input class="input" type="text" disabled value="{{ $data['rab']->nomor_rab }}" />
                        </div>
                        <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                            <label class="form-label max-w-56">Divisi</label>
                            <input class="input" type="text" disabled value="{{ $data['rab']->divisi_id }}" />
                        </div>
                    </div>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                            <label class="form-label max-w-56">Bulan Pengajuan</label>
                            <input class="input" type="text" disabled
                                value="{{ $data['rab']->bulan_pengajuan->translatedFormat('F Y') }}" />
                        </div>
                        <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                            <label class="form-label max-w-56">Status</label>
                            <span class="badge badge-{{ $data['rab']->status_color }} badge-outline">
                                {{ $data['rab']->status_label }}
                            </span>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                            <label class="form-label max-w-56">Total Kegiatan</label>
                            <input class="input" type="text" disabled value="{{ $data['rab']->total_kegiatan }} kegiatan" />
                        </div>
                        <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                            <label class="form-label max-w-56">Total Biaya Anggaran</label>
                            <input class="input" type="text" disabled
                                value="Rp {{ number_format($data['rab']->total_biaya_anggaran, 0, ',', '.') }}" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Items Table -->
            <div class="card pb-2.5">
                <div class="card-header">
                    <h3 class="card-title">Daftar Kegiatan</h3>
                </div>
                <div class="card-body">
                    <div class="scrollable-x-auto">
                        <table class="table table-auto table-border align-middle text-gray-700 font-medium text-sm">
                            <thead>
                                <tr>
                                    <th class="w-10 text-center">No</th>
                                    <th>Kegiatan</th>
                                    <th>Catatan Kegiatan</th>
                                    <th class="text-right">Biaya Anggaran</th>
                                    <th>Waktu Pelaksanaan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($data['rab']->items as $index => $item)
                                    <tr>
                                        <td class="text-center">{{ $index + 1 }}</td>
                                        <td>{{ $item->kegiatan }}</td>
                                        <td class="max-w-xs">
                                            @if($item->catatan_kegiatan)
                                                <div class="prose prose-sm max-w-none">
                                                    {!! $item->catatan_kegiatan !!}
                                                </div>
                                            @else
                                                <span class="text-gray-400 text-xs">-</span>
                                            @endif
                                        </td>
                                        <td class="text-right">
                                            Rp {{ number_format($item->biaya_anggaran, 0, ',', '.') }}
                                        </td>
                                        <td>{{ $item->waktu_pelaksanaan }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-gray-400">Tidak ada item kegiatan.</td>
                                    </tr>
                                @endforelse
                            @if($data['rab']->items->isNotEmpty())

                                <tr class="font-semibold text-gray-900">
                                        <td colspan="3" class="text-right">Total</td>
                                        <td class="text-right">
                                            Rp {{ number_format($data['rab']->total_biaya_anggaran, 0, ',', '.') }}
                                        </td>
                                        <td></td>
                                    </tr>
                            @endif

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Approval Log Timeline -->
            <div class="card pb-2.5">
                <div class="card-header">
                    <h3 class="card-title">Riwayat Persetujuan</h3>
                </div>
                <div class="card-body">
                    @php
                        $logs = $data['rab']->approvalLogs->sortByDesc('created_at');
                        $aksiColors = [
                            'SUBMITTED'          => 'info',
                            'APPROVED_BENDAHARA' => 'success',
                            'REJECTED_BENDAHARA' => 'danger',
                            'APPROVED_KETUA'     => 'success',
                            'REJECTED_KETUA'     => 'danger',
                            'CANCELLED'          => 'dark',
                        ];
                        $aksiLabels = [
                            'SUBMITTED'          => 'Diajukan',
                            'APPROVED_BENDAHARA' => 'Disetujui Bendahara',
                            'REJECTED_BENDAHARA' => 'Ditolak Bendahara',
                            'APPROVED_KETUA'     => 'Disetujui Ketua',
                            'REJECTED_KETUA'     => 'Ditolak Ketua',
                            'CANCELLED'          => 'Dibatalkan',
                        ];
                    @endphp

                    @if($logs->isEmpty())
                        <p class="text-gray-400 text-sm">Belum ada riwayat persetujuan.</p>
                    @else
                        <div class="flex flex-col gap-4">
                            @foreach($logs as $log)
                                @php
                                    $aksiKey = $log->aksi instanceof \App\Enums\RabAksiLog ? $log->aksi->value : (string) $log->aksi;
                                    $color   = $aksiColors[$aksiKey] ?? 'secondary';
                                    $label   = $aksiLabels[$aksiKey] ?? $aksiKey;
                                @endphp
                                <div class="flex gap-4 items-start">
                                    <div class="flex flex-col items-center">
                                        <span class="badge badge-{{ $color }} badge-dot size-3 mt-1"></span>
                                        @if(!$loop->last)
                                            <div class="w-px flex-1 bg-gray-200 mt-1" style="min-height: 24px;"></div>
                                        @endif
                                    </div>
                                    <div class="flex flex-col gap-1 pb-2">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <span class="badge badge-{{ $color }} badge-outline text-xs">{{ $label }}</span>
                                            <span class="text-sm font-medium text-gray-800">
                                                {{ $log->dilakukanOleh?->name ?? '-' }}
                                            </span>
                                            <span class="text-xs text-gray-500">
                                                {{ $log->created_at->format('d M Y, H:i') }}
                                            </span>
                                        </div>
                                        @if($log->catatan)
                                            <p class="text-sm text-gray-600 italic">"{{ $log->catatan }}"</p>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
    <!-- End of Container -->
@endsection
