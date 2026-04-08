@extends('layouts.main')

@section('content')
    <form action="{{ route('rab.update', $data['rab']->id) }}" method="post" id="rab-form">
        @csrf
        @method('PUT')
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
                    <button type="submit" class="btn btn-sm text-center btn-success">
                        <i class="ki-filled ki-check"></i>Simpan Perubahan
                    </button>
                </div>
            </div>
        </div>

        @include('partials.attention')

        <div class="container-fixed">
            <div class="grid gap-5 mx-auto">

                <div class="card pb-2.5">
                    <div class="card-header">
                        <h3 class="card-title">Informasi RAB</h3>
                    </div>
                    <div class="card-body grid gap-5">
                        <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                            <label class="form-label max-w-56">Nomor RAB</label>
                            <input class="input" type="text" disabled value="{{ $data['rab']->nomor_rab }}" />
                        </div>
                        <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                            <label class="form-label max-w-56">
                                Bulan Pengajuan <span class="text-danger">*</span>
                            </label>
                            <input class="input" name="bulan_pengajuan" type="month" required
                                value="{{ old('bulan_pengajuan', $data['rab']->bulan_pengajuan->format('Y-m')) }}" />
                        </div>
                    </div>
                </div>

                <div class="card pb-2.5">
                    <div class="card-header">
                        <h3 class="card-title">Daftar Kegiatan</h3>
                        <div class="card-toolbar">
                            <button type="button" id="tambah-item-btn" class="btn btn-sm btn-success">
                                <i class="ki-filled ki-plus"></i>Tambah Item
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="rab-items-container"></div>
                    </div>
                </div>

            </div>
        </div>
    </form>
@endsection

@push('javascript')
    <script>
        let itemIndex = 0;

        function escapeHtml(str) {
            const div = document.createElement('div');
            div.appendChild(document.createTextNode(String(str)));
            return div.innerHTML;
        }

        function formatBiaya(val) {
            const num = parseFloat(String(val).replace(/\./g, '').replace(/,/g, ''));
            return isNaN(num) || num === 0 ? '' : num.toLocaleString('id-ID');
        }

        function createItemRow(index, data = {}) {
            const row = document.createElement('div');
            row.className = 'rab-item-row border border-gray-200 rounded-lg p-4 mb-4';
            row.innerHTML = `
                <div class="flex justify-between items-center mb-3">
                    <span class="text-sm font-semibold text-gray-700">Item #<span class="item-number">${index + 1}</span></span>
                    <button type="button" class="btn btn-icon btn-sm btn-clear btn-danger hapus-item-btn" title="Hapus item">
                        <i class="ki-filled ki-trash"></i>
                    </button>
                </div>
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-3">
                    <div class="flex flex-col gap-1">
                        <label class="form-label">Kegiatan <span class="text-danger">*</span></label>
                        <input class="input" type="text" name="items[${index}][kegiatan]"
                            placeholder="Nama kegiatan" required value="${escapeHtml(data.kegiatan ?? '')}">
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="form-label">Waktu Pelaksanaan <span class="text-danger">*</span></label>
                        <input class="input" type="text" name="items[${index}][waktu_pelaksanaan]"
                            placeholder="Contoh: Januari 2025" required value="${escapeHtml(data.waktu_pelaksanaan ?? '')}">
                    </div>
                </div>
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-3">
                    <div class="flex flex-col gap-1">
                        <label class="form-label">Biaya Anggaran (Rp) <span class="text-danger">*</span></label>
                        <input class="input biaya-input" type="text" name="items[${index}][biaya_anggaran]"
                            placeholder="0" required value="${formatBiaya(data.biaya_anggaran ?? '')}">
                    </div>
                </div>
                <div class="flex flex-col gap-1">
                    <label class="form-label">Catatan Kegiatan</label>
                    <textarea class="textarea" name="items[${index}][catatan_kegiatan]"
                        rows="4" placeholder="Catatan tambahan...">${escapeHtml(data.catatan_kegiatan ?? '')}</textarea>
                </div>
            `;

            row.querySelector('.hapus-item-btn').addEventListener('click', function () {
                const container = document.getElementById('rab-items-container');
                if (container.querySelectorAll('.rab-item-row').length <= 1) {
                    alert('Minimal harus ada 1 item kegiatan.');
                    return;
                }
                row.remove();
                renumberItems();
            });

            return row;
        }

        function renumberItems() {
            document.querySelectorAll('.rab-item-row').forEach((row, i) => {
                const num = row.querySelector('.item-number');
                if (num) num.textContent = i + 1;
            });
        }

        function addItem(data = {}) {
            const container = document.getElementById('rab-items-container');
            container.appendChild(createItemRow(itemIndex++, data));
        }

        document.getElementById('tambah-item-btn').addEventListener('click', () => addItem());

        // Format comma separator on biaya input
        document.getElementById('rab-items-container').addEventListener('input', function (e) {
            if (!e.target.classList.contains('biaya-input')) return;
            const raw = e.target.value.replace(/\D/g, '');
            e.target.value = raw ? parseInt(raw).toLocaleString('id-ID') : '';
        });

        // Strip commas before submit
        document.getElementById('rab-form').addEventListener('submit', function () {
            document.querySelectorAll('.biaya-input').forEach(input => {
                input.value = input.value.replace(/\./g, '').replace(/,/g, '');
            });
        });

        @if(old('items'))
            @foreach(old('items', []) as $item)
                addItem({
                    kegiatan: @json($item['kegiatan'] ?? ''),
                    waktu_pelaksanaan: @json($item['waktu_pelaksanaan'] ?? ''),
                    biaya_anggaran: @json($item['biaya_anggaran'] ?? ''),
                    catatan_kegiatan: @json($item['catatan_kegiatan'] ?? ''),
                });
            @endforeach
        @else
            @foreach($data['rab']->items as $item)
                addItem({
                    kegiatan: @json($item->kegiatan),
                    waktu_pelaksanaan: @json($item->waktu_pelaksanaan),
                    biaya_anggaran: @json((string) $item->biaya_anggaran),
                    catatan_kegiatan: @json($item->catatan_kegiatan ?? ''),
                });
            @endforeach
            @if($data['rab']->items->isEmpty())
                addItem();
            @endif
        @endif
    </script>
@endpush
