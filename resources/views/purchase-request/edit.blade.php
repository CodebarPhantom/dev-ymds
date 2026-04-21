@extends('layouts.main')

@section('content')
    <form action="{{ route('purchase-requests.update', $data['purchaseRequest']->id) }}" method="post" id="pr-form">
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
                    <a class="btn text-center btn-sm btn-primary" href="{{ route('purchase-requests.index') }}">
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
                        <h3 class="card-title">Informasi Pengajuan</h3>
                    </div>
                    <div class="card-body grid gap-5">
                        <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                            <label class="form-label max-w-56">Nomor Pengajuan</label>
                            <input class="input" type="text" disabled
                                value="{{ $data['purchaseRequest']->nomor_pengajuan }}" />
                        </div>
                        <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                            <label class="form-label max-w-56">
                                Judul Pengajuan <span class="text-danger">*</span>
                            </label>
                            <input class="input" name="judul_pengajuan" type="text" required
                                placeholder="Masukkan judul pengajuan"
                                value="{{ old('judul_pengajuan', $data['purchaseRequest']->judul_pengajuan) }}" />
                        </div>
                        <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                            <label class="form-label max-w-56">
                                Tanggal Dibutuhkan <span class="text-danger">*</span>
                            </label>
                            <input class="input" name="tanggal_dibutuhkan" type="date" required
                                value="{{ old('tanggal_dibutuhkan', $data['purchaseRequest']->tanggal_dibutuhkan?->format('Y-m-d')) }}" />
                        </div>
                        <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                            <label class="form-label max-w-56">Keperluan</label>
                            <div class="w-full">
                                <textarea name="keperluan" class="textarea" rows="4" placeholder="Tuliskan keperluan pengajuan...">{{ old('keperluan', $data['purchaseRequest']->keperluan) }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card pb-2.5">
                    <div class="card-header">
                        <h3 class="card-title">Daftar Barang</h3>
                        <div class="card-toolbar">
                            <button type="button" id="tambah-item-btn" class="btn btn-sm btn-success">
                                <i class="ki-filled ki-plus"></i>Tambah Item
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="pr-items-container"></div>
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
            row.className = 'pr-item-row border border-gray-200 rounded-lg p-4 mb-4';
            row.innerHTML = `
                <div class="flex justify-between items-center mb-3">
                    <span class="text-sm font-semibold text-gray-700">Item #<span class="item-number">${index + 1}</span></span>
                    <button type="button" class="btn btn-icon btn-sm btn-clear btn-danger hapus-item-btn" title="Hapus item">
                        <i class="ki-filled ki-trash"></i>
                    </button>
                </div>
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-3">
                    <div class="flex flex-col gap-1">
                        <label class="form-label">Nama Barang <span class="text-danger">*</span></label>
                        <input class="input" type="text" name="items[${index}][nama_barang]"
                            placeholder="Nama barang" required value="${escapeHtml(data.nama_barang ?? '')}">
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="form-label">Satuan <span class="text-danger">*</span></label>
                        <input class="input" type="text" name="items[${index}][satuan]"
                            placeholder="Contoh: pcs, kg, unit" required value="${escapeHtml(data.satuan ?? '')}">
                    </div>
                </div>
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-3">
                    <div class="flex flex-col gap-1">
                        <label class="form-label">Jumlah <span class="text-danger">*</span></label>
                        <input class="input" type="number" name="items[${index}][jumlah]"
                            placeholder="0" min="1" required value="${escapeHtml(data.jumlah ?? '')}">
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="form-label">Biaya Estimasi per Satuan (Rp) <span class="text-danger">*</span></label>
                        <input class="input biaya-input" type="text" name="items[${index}][biaya_estimasi]"
                            placeholder="0" required value="${formatBiaya(data.biaya_estimasi ?? '')}">
                    </div>
                </div>
                <div class="flex flex-col gap-1">
                    <label class="form-label">Catatan Item</label>
                    <textarea class="textarea" name="items[${index}][catatan_item]"
                        rows="3" placeholder="Catatan tambahan...">${escapeHtml(data.catatan_item ?? '')}</textarea>
                </div>
            `;

            row.querySelector('.hapus-item-btn').addEventListener('click', function () {
                const container = document.getElementById('pr-items-container');
                if (container.querySelectorAll('.pr-item-row').length <= 1) {
                    alert('Minimal harus ada 1 item barang.');
                    return;
                }
                row.remove();
                renumberItems();
            });

            return row;
        }

        function renumberItems() {
            document.querySelectorAll('.pr-item-row').forEach((row, i) => {
                const num = row.querySelector('.item-number');
                if (num) num.textContent = i + 1;
            });
        }

        function addItem(data = {}) {
            const container = document.getElementById('pr-items-container');
            container.appendChild(createItemRow(itemIndex++, data));
        }

        document.getElementById('tambah-item-btn').addEventListener('click', () => addItem());

        // Format thousand separator on biaya input
        document.getElementById('pr-items-container').addEventListener('input', function (e) {
            if (!e.target.classList.contains('biaya-input')) return;
            const raw = e.target.value.replace(/\D/g, '');
            e.target.value = raw ? parseInt(raw).toLocaleString('id-ID') : '';
        });

        // Strip separators before submit
        document.getElementById('pr-form').addEventListener('submit', function () {
            document.querySelectorAll('.biaya-input').forEach(input => {
                input.value = input.value.replace(/\./g, '').replace(/,/g, '');
            });
        });

        @if(old('items'))
            @foreach(old('items', []) as $item)
                addItem({
                    nama_barang: @json($item['nama_barang'] ?? ''),
                    satuan: @json($item['satuan'] ?? ''),
                    jumlah: @json($item['jumlah'] ?? ''),
                    biaya_estimasi: @json($item['biaya_estimasi'] ?? ''),
                    catatan_item: @json($item['catatan_item'] ?? ''),
                });
            @endforeach
        @else
            @foreach($data['purchaseRequest']->items as $item)
                addItem({
                    nama_barang: @json($item->nama_barang),
                    satuan: @json($item->satuan),
                    jumlah: @json((string) $item->jumlah),
                    biaya_estimasi: @json((string) $item->biaya_estimasi),
                    catatan_item: @json($item->catatan_item ?? ''),
                });
            @endforeach
            @if($data['purchaseRequest']->items->isEmpty())
                addItem();
            @endif
        @endif
    </script>
@endpush
