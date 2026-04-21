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
                <a class="btn text-center btn-sm btn-primary" href="{{ route('purchase-requests.index') }}">
                    <i class="ki-filled ki-left"></i>Kembali
                </a>
            </div>
        </div>
    </div>

    @include('partials.attention')

    <div class="container-fixed">
        <div class="grid gap-5 mx-auto">

            <!-- Header Card -->
            <div class="card pb-2.5">
                <div class="card-header">
                    <h3 class="card-title">Informasi Pengajuan</h3>
                </div>
                <div class="card-body grid gap-4">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                            <label class="form-label max-w-56">Nomor Pengajuan</label>
                            <input class="input" type="text" disabled value="{{ $data['purchaseRequest']->nomor_pengajuan }}" />
                        </div>
                        <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                            <label class="form-label max-w-56">Divisi</label>
                            <input class="input" type="text" disabled value="{{ $data['purchaseRequest']->divisi_id }}" />
                        </div>
                    </div>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                            <label class="form-label max-w-56">Judul Pengajuan</label>
                            <input class="input" type="text" disabled value="{{ $data['purchaseRequest']->judul_pengajuan }}" />
                        </div>
                        <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                            <label class="form-label max-w-56">Tanggal Dibutuhkan</label>
                            <input class="input" type="text" disabled
                                value="{{ $data['purchaseRequest']->tanggal_dibutuhkan->format('d M Y') }}" />
                        </div>
                    </div>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                            <label class="form-label max-w-56">Status</label>
                            <span class="badge badge-{{ $data['purchaseRequest']->status_color }} badge-outline">
                                {{ $data['purchaseRequest']->status_label }}
                            </span>
                        </div>
                        <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                            <label class="form-label max-w-56">Total Item</label>
                            <input class="input" type="text" disabled value="{{ $data['purchaseRequest']->total_item }} item" />
                        </div>
                    </div>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                            <label class="form-label max-w-56">Total Biaya Estimasi</label>
                            <input class="input" type="text" disabled
                                value="Rp {{ number_format($data['purchaseRequest']->total_biaya_estimasi, 0, ',', '.') }}" />
                        </div>
                        <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                            <label class="form-label max-w-56">Total Biaya Aktual</label>
                            <input class="input" type="text" disabled
                                value="Rp {{ number_format($data['purchaseRequest']->total_biaya_aktual, 0, ',', '.') }}" />
                        </div>
                    </div>
                    @if($data['purchaseRequest']->keperluan)
                        <div class="flex flex-col gap-2">
                            <label class="form-label">Keperluan</label>
                            <div class="prose prose-sm max-w-none border rounded p-3 bg-gray-50">
                                {!! $data['purchaseRequest']->keperluan !!}
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Items Table -->
            <div class="card pb-2.5">
                <div class="card-header">
                    <h3 class="card-title">Daftar Barang</h3>
                </div>
                <div class="card-body">
                    <div class="scrollable-x-auto">
                        @php
                            $isInPurchasing = $data['purchaseRequest']->isInPurchasing();
                        @endphp
                        <table class="table table-auto table-border align-middle text-gray-700 font-medium text-sm">
                            <thead>
                                <tr>
                                    <th class="w-10 text-center">No</th>
                                    <th>Nama Barang</th>
                                    <th>Satuan</th>
                                    <th class="text-right">Jumlah</th>
                                    <th class="text-right">Biaya Estimasi</th>
                                    @if($isInPurchasing)
                                        <th class="text-center">Sudah Dibeli</th>
                                        <th class="text-right">Harga Aktual</th>
                                    @endif
                                    <th>Catatan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($data['purchaseRequest']->items as $index => $item)
                                    <tr>
                                        <td class="text-center">{{ $index + 1 }}</td>
                                        <td>{{ $item->nama_barang }}</td>
                                        <td>{{ $item->satuan }}</td>
                                        <td class="text-right">{{ number_format($item->jumlah, 0, ',', '.') }}</td>
                                        <td class="text-right">
                                            Rp {{ number_format($item->biaya_estimasi, 0, ',', '.') }}
                                        </td>
                                        @if($isInPurchasing)
                                            <td class="text-center">
                                                @if($item->sudah_dibeli)
                                                    <span class="badge badge-success badge-outline">Ya</span>
                                                @else
                                                    <span class="badge badge-secondary badge-outline">Belum</span>
                                                @endif
                                            </td>
                                            <td class="text-right">
                                                @if($item->harga_aktual !== null)
                                                    Rp {{ number_format($item->harga_aktual, 0, ',', '.') }}
                                                @else
                                                    <span class="text-gray-400 text-xs">-</span>
                                                @endif
                                            </td>
                                        @endif
                                        <td class="max-w-xs">
                                            @if($item->catatan_item)
                                                {{ $item->catatan_item }}
                                            @else
                                                <span class="text-gray-400 text-xs">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ $isInPurchasing ? 8 : 6 }}" class="text-center text-gray-400">
                                            Tidak ada item barang.
                                        </td>
                                    </tr>
                                @endforelse
                                @if($data['purchaseRequest']->items->isNotEmpty())
                                    <tr class="font-semibold text-gray-900">
                                        <td colspan="{{ $isInPurchasing ? 4 : 4 }}" class="text-right">Total</td>
                                        <td class="text-right">
                                            Rp {{ number_format($data['purchaseRequest']->total_biaya_estimasi, 0, ',', '.') }}
                                        </td>
                                        @if($isInPurchasing)
                                            <td></td>
                                            <td class="text-right">
                                                Rp {{ number_format($data['purchaseRequest']->total_biaya_aktual, 0, ',', '.') }}
                                            </td>
                                        @endif
                                        <td></td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Log Timeline -->
            <div class="card pb-2.5">
                <div class="card-header">
                    <h3 class="card-title">Riwayat Aktivitas</h3>
                </div>
                <div class="card-body">
                    @php
                        $logs = $data['purchaseRequest']->logs->sortByDesc('created_at');
                        $aksiColors = [
                            'SUBMITTED'           => 'info',
                            'APPROVED_BENDAHARA'  => 'success',
                            'REJECTED_BENDAHARA'  => 'danger',
                            'APPROVED_KETUA'      => 'success',
                            'REJECTED_KETUA'      => 'danger',
                            'CANCELLED'           => 'dark',
                            'PURCHASING_STARTED'  => 'primary',
                            'ITEM_PURCHASED'      => 'warning',
                            'COMPLETED'           => 'success',
                        ];
                        $aksiLabels = [
                            'SUBMITTED'           => 'Diajukan',
                            'APPROVED_BENDAHARA'  => 'Disetujui Bendahara',
                            'REJECTED_BENDAHARA'  => 'Ditolak Bendahara',
                            'APPROVED_KETUA'      => 'Disetujui Ketua',
                            'REJECTED_KETUA'      => 'Ditolak Ketua',
                            'CANCELLED'           => 'Dibatalkan',
                            'PURCHASING_STARTED'  => 'Pembelian Dimulai',
                            'ITEM_PURCHASED'      => 'Item Dibeli',
                            'COMPLETED'           => 'Selesai',
                        ];
                    @endphp

                    @if($logs->isEmpty())
                        <p class="text-gray-400 text-sm">Belum ada riwayat aktivitas.</p>
                    @else
                        <div class="flex flex-col gap-4">
                            @foreach($logs as $log)
                                @php
                                    $aksiKey = $log->aksi instanceof \App\Enums\PurchaseRequestAksiLog
                                        ? $log->aksi->value
                                        : (string) $log->aksi;
                                    $color = $aksiColors[$aksiKey] ?? 'secondary';
                                    $label = $aksiLabels[$aksiKey] ?? $aksiKey;
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
