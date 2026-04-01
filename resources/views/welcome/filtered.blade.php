<!-- Locations List -->
<div class="mb-8">
    <div class="bg-white rounded-xl p-5 shadow-lg animate-slide-up" style="animation-delay: 0.2s">
        <h3 class="font-semibold text-gray-900 mb-3 flex items-center gap-2">
            <i class="bi bi-pencil-square text-indigo-600"></i>
            Konfirmasi Pembayaran (Form)
        </h3>

        <form id="confirmForm" class="space-y-3">
            <div>
                <label class="text-sm font-medium text-gray-700">Pilih Rumah / Lokasi</label>
                <select id="locationSelect" class="w-full mt-2 border rounded-lg px-3 py-2 text-sm">
                    <option value="">-- Pilih Rumah --</option>
                    @foreach ($locations as $loc)
                        <option value="{{ $loc->id }}">{{ $loc->name ?? 'Rumah ' . $loc->id }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="text-sm font-medium text-gray-700">Nama Pengirim</label>
                <input id="payerName" type="text" placeholder="Nama Anda"
                    class="w-full mt-2 border rounded-lg px-3 py-2 text-sm" />
            </div>

            <div>
                <label class="text-sm font-medium text-gray-700">Bukti Transfer (gambar / pdf)</label>
                <input id="attachment" type="file" accept="image/*,.pdf" class="w-full mt-2 text-sm" />
                <p id="attachmentHelp" class="text-xs text-gray-500 mt-1">Opsional, tapi disarankan untuk mempercepat
                    verifikasi.</p>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="text-sm font-medium text-gray-700">Bulan</label>
                    <select id="monthSelect" class="w-full mt-2 border rounded-lg px-3 py-2 text-sm">
                        <option value="">-- Pilih Bulan --</option>
                        @php $months = [1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember']; @endphp
                        @foreach ($months as $mKey => $mName)
                            <option value="{{ $mKey }}">{{ $mName }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-700">Tahun</label>
                    <select id="yearSelect" class="w-full mt-2 border rounded-lg px-3 py-2 text-sm">
                        <option value="">-- Pilih Tahun --</option>
                        @php $year = date('Y'); @endphp
                        @for ($y = $year; $y >= $year - 2; $y--)
                            <option value="{{ $y }}">{{ $y }}</option>
                        @endfor
                    </select>
                </div>
            </div>



            <!-- Catatan -->
            <div>
                <label class="text-sm font-medium text-gray-700">Catatan (opsional)</label>
                <textarea id="notes" rows="3" maxlength="500" placeholder="Tambahkan catatan singkat untuk admin, mis. alasan telat bayar atau no. invoice"
                    class="w-full mt-2 border rounded-lg px-3 py-2 text-sm resize-y"></textarea>
                <div class="flex justify-between items-center mt-1 text-xs text-gray-400">
                    <div>Catatan akan dikirim bersama konfirmasi.</div>
                    <div id="notesCount">0/500</div>
                </div>
            </div>

            <div class="flex gap-2">
                <button id="prepareBtn" type="button"
                    class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition-colors">
                    Siapkan Konfirmasi
                </button>
            </div>
        </form>

        <!-- Preview area -->
        <div id="previewArea" class="mt-4 hidden">
            <div class="bg-indigo-50 border border-indigo-100 rounded-lg p-4">
                <h4 class="font-semibold text-indigo-700 mb-2">Preview Konfirmasi</h4>
                <div id="previewSummary" class="text-sm text-gray-700"></div>
                <div id="previewFile" class="mt-3"></div>

                <div class="mt-3 flex gap-2">
                    <button id="sendWhatsAppBtn"
                        class="bg-green-500 text-white px-3 py-2 rounded-lg text-sm hover:bg-green-600">
                        Kirim via WhatsApp ke Admin
                    </button>
                    <button id="downloadJsonBtn"
                        class="bg-gray-200 text-gray-800 px-3 py-2 rounded-lg text-sm hover:bg-gray-300">
                        Unduh (JSON)
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
