<!-- Payment Options Section -->
<div class="mb-10">
    <h2 class="text-2xl font-bold mb-6 flex items-center gap-3 font-poppins">
        <span class="w-1 h-8 bg-gradient-to-b from-blue-500 to-green-500 rounded-full"></span>
        Informasi Pembayaran
    </h2>

    <!-- Tabs -->
    <div class="tab-container mb-6">
        <div class="tab-indicator" id="tabIndicator"></div>
        <button class="tab-button active" onclick="switchTab('bendahara')">Bendahara RT</button>
        <button class="tab-button" onclick="switchTab('masjid')">Rekening Masjid</button>
    </div>

    <!-- Bendahara RT Content -->
    <div id="bendaharaContent" class="space-y-4">
        <!-- Bendahara 1 -->
        <div class="bank-card">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 rounded-2xl bg-blue-100 flex items-center justify-center">
                        <i class="fas fa-university text-blue-500 text-2xl"></i>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500">Bendahara 1</div>
                        <div class="text-xl font-semibold font-quicksand">Sea Bank</div>
                    </div>
                </div>
                <div class="text-right">
                    <div class="text-sm text-gray-500">A/n</div>
                    <div class="font-semibold font-quicksand">Anggia Yulita</div>
                </div>
            </div>
            <div class="bg-gray-50 rounded-xl p-4">
                <div class="flex items-center justify-between gap-3">
                    <div class="font-mono text-xl">901048170909</div>
                    <button onclick="copyToClipboard('901048170909', 'Sea Bank')" class="btn btn-primary">
                        <i class="bi bi-clipboard"></i> Salin
                    </button>
                </div>
            </div>
        </div>

        <!-- Bendahara 2 -->
        <div class="bank-card">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 rounded-2xl bg-green-100 flex items-center justify-center">
                        <i class="fas fa-university text-green-500 text-2xl"></i>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500">Bendahara 2</div>
                        <div class="text-xl font-semibold font-quicksand">BNI</div>
                    </div>
                </div>
                <div class="text-right">
                    <div class="text-sm text-gray-500">A/n</div>
                    <div class="font-semibold font-quicksand">Virna Melinda Rahmawati</div>
                </div>
            </div>
            <div class="bg-gray-50 rounded-xl p-4">
                <div class="flex items-center justify-between gap-3">
                    <div class="font-mono text-xl">0573247605</div>
                    <button onclick="copyToClipboard('0573247605', 'BNI')" class="btn btn-primary">
                        <i class="bi bi-clipboard"></i> Salin
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Masjid Content -->
    <div id="masjidContent" class="space-y-4 hidden">
        <!-- Rekening Masjid -->
        <div class="bank-card">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 rounded-2xl bg-purple-100 flex items-center justify-center">
                        <i class="fas fa-mosque text-purple-500 text-2xl"></i>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500">Rekening Masjid</div>
                        <div class="text-xl font-semibold font-quicksand">Bank Muamalat</div>
                    </div>
                </div>
                <div class="text-right">
                    <div class="text-sm text-gray-500">A/n</div>
                    <div class="font-semibold font-quicksand">MASJID AN-NAHL</div>
                </div>
            </div>
            <div class="bg-gray-50 rounded-xl p-4">
                <div class="flex items-center justify-between gap-3">
                    <div class="font-mono text-xl">3410023333</div>
                    <button onclick="copyToClipboard('3410023333', 'Bank Muamalat')" class="btn btn-primary">
                        <i class="bi bi-clipboard"></i> Salin
                    </button>
                </div>
            </div>
            <div class="mt-3 text-xs text-gray-500 flex items-center gap-2">
                <i class="fas fa-info-circle"></i> Kode Bank: 147
            </div>
        </div>

        <!-- Konfirmasi Pembayaran -->
        <div class="card p-6">
            <h3 class="text-lg font-semibold mb-4 flex items-center gap-2 font-quicksand">
                <i class="fas fa-check-circle text-green-500"></i>
                Konfirmasi Pembayaran
            </h3>
            <div class="space-y-3">
                <a href="https://wa.me/6282213660543" target="_blank" class="contact-chip flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center">
                            <i class="fab fa-whatsapp text-green-500 text-xl"></i>
                        </div>
                        <div>
                            <div class="font-semibold font-quicksand">Ust Gunawan</div>
                            <div class="text-sm text-gray-500">0822-1366-0543</div>
                        </div>
                    </div>
                    <i class="fas fa-arrow-right text-green-500"></i>
                </a>

                <a href="https://wa.me/6287879066640" target="_blank" class="contact-chip flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center">
                            <i class="fab fa-whatsapp text-green-500 text-xl"></i>
                        </div>
                        <div>
                            <div class="font-semibold font-quicksand">Ust Amir Mahmud</div>
                            <div class="text-sm text-gray-500">0878-7906-6640</div>
                        </div>
                    </div>
                    <i class="fas fa-arrow-right text-green-500"></i>
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Admin RT Section -->
<div class="mb-10">
    <h2 class="text-2xl font-bold mb-6 flex items-center gap-3 font-poppins">
        <span class="w-1 h-8 bg-gradient-to-b from-blue-500 to-green-500 rounded-full"></span>
        Hubungi Admin
    </h2>
    <div class="card p-6">
        <div class="space-y-3">
            <a href="https://wa.me/{{ $admin_whatsapp ?? '6281313144088' }}?text=Halo%20saya%20ingin%20membayar%20iuran%20RT%20037" target="_blank" class="contact-chip flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center">
                        <i class="fab fa-whatsapp text-blue-500 text-xl"></i>
                    </div>
                    <div>
                        <div class="font-semibold font-quicksand">WhatsApp Admin</div>
                        <div class="text-sm text-gray-500">{{ $admin_whatsapp ?? '6281313144088' }}</div>
                    </div>
                </div>
                <i class="fas fa-arrow-right text-blue-500"></i>
            </a>

            {{-- <a href="mailto:{{ $admin_email ?? 'rukuntetanggavph.037@mail.com' }}" class="contact-chip flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center">
                        <i class="fas fa-envelope text-green-500 text-xl"></i>
                    </div>
                    <div>
                        <div class="font-semibold font-quicksand">Email Admin</div>
                        <div class="text-sm text-gray-500">{{ $admin_email ?? 'rukuntetanggavph.037@mail.com' }}</div>
                    </div>
                </div>
                <i class="fas fa-arrow-right text-green-500"></i>
            </a> --}}
        </div>
    </div>
</div>
<!-- Footer -->
<footer class="mt-12 text-center text-gray-600 text-sm">
    <p>&copy; 2025 rumio - Membangun komunitas modern</p>
    <p class="text-xs mt-1 text-gray-500">Semua tercatat & transparan</p>
</footer>
