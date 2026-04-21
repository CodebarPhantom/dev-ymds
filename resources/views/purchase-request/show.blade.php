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
                        <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                            <label class="form-label max-w-56">Keperluan</label>
                            <textarea class="textarea" rows="4" disabled>{{ $data['purchaseRequest']->keperluan }}</textarea>
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
                            $showPurchasingCols = $data['purchaseRequest']->isInPurchasing()
                                || $data['purchaseRequest']->status === \App\Enums\PurchaseRequestStatus::COMPLETED;
                            $canMarkPurchased = $data['purchaseRequest']->isInPurchasing();
                        @endphp
                        <table class="table table-auto table-border align-middle text-gray-700 font-medium text-sm">
                            <thead>
                                <tr>
                                    <th class="w-10 text-center">No</th>
                                    <th>Nama Barang</th>
                                    <th>Satuan</th>
                                    <th class="text-right">Jumlah</th>
                                    <th class="text-right">Biaya Estimasi</th>
                                    <th class="text-right">Total Biaya</th>
                                    @if($showPurchasingCols)
                                        <th class="text-center">Sudah Dibeli</th>
                                        <th class="text-right">Harga Aktual</th>
                                    @endif
                                    <th>Catatan</th>
                                    @can('purchasingPolicy', $data['purchaseRequest'])
                                        @if($canMarkPurchased)
                                            <th class="text-center">Aksi</th>
                                        @endif
                                    @endcan
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
                                        <td class="text-right">
                                            Rp {{ number_format($item->jumlah * $item->biaya_estimasi, 0, ',', '.') }}
                                        </td>
                                        @if($showPurchasingCols)
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
                                        @can('purchasingPolicy', $data['purchaseRequest'])
                                            @if($canMarkPurchased)
                                                <td class="text-center">
                                                    @if(!$item->sudah_dibeli)
                                                        <button type="button"
                                                            class="btn btn-xs btn-success"
                                                            onclick="openMarkPurchasedModal({{ $item->id }}, '{{ addslashes($item->nama_barang) }}', '{{ route('api.v1.purchase-requests.items.mark-purchased', [$data['purchaseRequest']->id, $item->id]) }}')">
                                                            <i class="ki-filled ki-check"></i> Tandai Dibeli
                                                        </button>
                                                    @endif
                                                </td>
                                            @endif
                                        @endcan
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ $showPurchasingCols ? 9 : 7 }}" class="text-center text-gray-400">
                                            Tidak ada item barang.
                                        </td>
                                    </tr>
                                @endforelse
                                @if($data['purchaseRequest']->items->isNotEmpty())
                                    <tr class="font-semibold text-gray-900">
                                        <td colspan="5" class="text-right">Total</td>
                                        <td class="text-right">
                                            Rp {{ number_format($data['purchaseRequest']->total_biaya_estimasi, 0, ',', '.') }}
                                        </td>
                                        @if($showPurchasingCols)
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
                            'APPROVED_BENDAHARA'  => 'Menunggu Persetujuan Ketua',
                            'REJECTED_BENDAHARA'  => 'Ditolak Bendahara',
                            'APPROVED_KETUA'      => 'Menunggu Proses Pembelian',
                            'REJECTED_KETUA'      => 'Ditolak Ketua',
                            'CANCELLED'           => 'Dibatalkan',
                            'PURCHASING_STARTED'  => 'Proses Pembelian',
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

@if($data['purchaseRequest']->isInPurchasing())
@push('javascript')
<script>
    let pendingMarkUrl = null;

    function openMarkPurchasedModal(itemId, namaBarang, url) {
        pendingMarkUrl = url;
        document.getElementById('modal-item-name').textContent = namaBarang;
        document.getElementById('input-harga-aktual').value = '';
        KTModal.getInstance(document.getElementById('modal_mark_purchased')).show();
    }

    document.getElementById('confirm-mark-purchased-btn').addEventListener('click', function () {
        const raw   = document.getElementById('input-harga-aktual').value.replace(/\./g, '').replace(/,/g, '');
        const harga = parseFloat(raw);

        if (isNaN(harga) || harga < 0) {
            alert('Harga aktual harus berupa angka dan tidak boleh negatif.');
            return;
        }

        fetch(pendingMarkUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ harga_aktual: harga }),
        })
        .then(r => r.json())
        .then(res => {
            KTModal.getInstance(document.getElementById('modal_mark_purchased')).hide();
            if (res.error) {
                alert(res.messages?.join('\n') ?? 'Terjadi kesalahan.');
                return;
            }
            window.location.reload();
        });
    });

    // Format thousand separator on harga input
    document.getElementById('input-harga-aktual').addEventListener('input', function () {
        const raw = this.value.replace(/\D/g, '');
        this.value = raw ? parseInt(raw).toLocaleString('id-ID') : '';
    });
</script>
@endpush

<!-- Modal Tandai Sudah Dibeli -->
<div class="modal hidden" id="modal_mark_purchased" data-modal="true">
    <div class="modal-content max-w-md top-[15%]">
        <div class="modal-header">
            <h3 class="modal-title">Tandai Sudah Dibeli</h3>
            <button class="btn btn-xs btn-icon btn-light" data-modal-dismiss="true">
                <i class="ki-outline ki-cross"></i>
            </button>
        </div>
        <div class="modal-body">
            <p class="text-sm text-gray-700 mb-3">
                Tandai item <strong id="modal-item-name"></strong> sebagai sudah dibeli.
            </p>
            <div class="flex flex-col gap-1">
                <label class="form-label">Harga Aktual (Rp) <span class="text-danger">*</span></label>
                <input id="input-harga-aktual" class="input" type="text" placeholder="0" />
                <span class="text-xs text-gray-400">Total harga aktual item yang sudah dibeli</span>
            </div>
        </div>
        <div class="modal-footer justify-end">
            <button class="btn btn-sm btn-light" data-modal-dismiss="true">Batal</button>
            <button id="confirm-mark-purchased-btn" class="btn btn-sm btn-success">
                <i class="ki-filled ki-check"></i> Konfirmasi
            </button>
        </div>
    </div>
</div>
@endif
