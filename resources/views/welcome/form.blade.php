<!-- Form Section -->
<div class="card p-6 mb-8">
    <h3 class="text-xl font-semibold mb-6 flex items-center gap-3 font-poppins">
        <span class="w-1 h-6 bg-gradient-to-b from-blue-500 to-green-500 rounded-full"></span>
        Form Pembayaran
    </h3>

    <form id="paymentForm" class="space-y-6">
        @if ($category->type !== 'block')
            <!-- Block Selection for non-block categories -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Pilih Blok</label>
                <select id="blockSelect" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm">
                    <option value="">-- Pilih Blok --</option>
                    @foreach ($blockCategories as $block)
                        <option value="{{ $block->id }}">{{ $block->name }}</option>
                    @endforeach
                </select>
            </div>
        @endif

        <!-- Location Selection -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
                @if ($category->type === 'block')
                    Pilih Rumah/Unit
                @else
                    Pilih Lokasi
                @endif
            </label>
            <select id="locationSelect" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm" {{ $category->type !== 'block' ? 'disabled' : '' }}>
                <option value="">-- Pilih Lokasi --</option>
                @if ($category->type === 'block')
                    @foreach ($locations as $location)
                        <option value="{{ $location->id }}">{{ $location->name }}</option>
                    @endforeach
                @endif
            </select>
        </div>

        <!-- Name -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Nama Lengkap</label>
            <input type="text" id="payerName" class="w-full border border-gray-300 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Masukkan nama lengkap">
        </div>

        <!-- Month and Year -->
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Bulan</label>
                <select id="monthSelect" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm">
                    <option value="">-- Pilih Bulan --</option>
                    @php 
                        $months = [
                            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                        ];
                        $currentMonth = date('n');
                    @endphp
                    @foreach ($months as $key => $month)
                        <option value="{{ $key }}" {{ $key == $currentMonth ? 'selected' : '' }}>{{ $month }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Tahun</label>
                <select id="yearSelect" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm">
                    <option value="">-- Pilih Tahun --</option>
                    @php 
                        $currentYear = date('Y');
                        $startYear = 2025;
                        $endYear = $currentYear + 2;
                    @endphp
                    @for ($year = $startYear; $year <= $endYear; $year++)
                        <option value="{{ $year }}" {{ $year == $currentYear ? 'selected' : '' }}>{{ $year }}</option>
                    @endfor
                </select>
            </div>
        </div>

        <!-- Amount -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Nominal (Rp)</label>
            <input type="text" id="amount" class="w-full border border-gray-300 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="0" inputmode="numeric">
        </div>

        <!-- Upload Proof -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Bukti Transfer</label>
            <input type="file" id="proofFile" accept="image/*,.pdf" class="w-full border border-gray-300 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            <p class="text-xs text-gray-500 mt-1">Format: JPG, PNG, PDF (Max 5MB)</p>
        </div>

        <!-- Notes -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Catatan (Opsional)</label>
            <textarea id="notes" rows="3" class="w-full border border-gray-300 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none" placeholder="Tambahkan catatan jika diperlukan..."></textarea>
        </div>

        <!-- Submit Button -->
        <button type="button" id="submitBtn" class="w-full btn btn-primary py-4 text-base font-semibold" data-category="{{ $category->name }}" data-category-type="{{ $category->type }}" data-category-id="{{ $category->id }}">
            <i class="fas fa-paper-plane mr-2"></i>
            Kirim Konfirmasi
        </button>
    </form>
</div>