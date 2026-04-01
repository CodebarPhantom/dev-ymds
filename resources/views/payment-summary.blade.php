<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Konfirmasi Berhasil - RT 037</title>
    <meta name="description" content="Konfirmasi pembayaran berhasil dikirim">

    <link rel="icon" type="image/jpeg" href="{{ asset('storage/rumio.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&family=Quicksand:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    @include('welcome.style')
</head>

<body class="font-poppins bg-pattern min-h-screen">
    <!-- Decorative Shapes -->
    <div class="shape shape-1"></div>
    <div class="shape shape-2"></div>
    <div class="shape shape-3"></div>

    <!-- Main Container -->
    <div class="min-h-screen flex flex-col items-center justify-center p-4">
        <div class="w-full max-w-2xl">
            <!-- Success Header -->
            <div class="text-center mb-8">
                <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-check text-green-600 text-3xl"></i>
                </div>
                <h1 class="text-2xl font-bold text-gray-800 mb-2">Konfirmasi Berhasil Dikirim!</h1>
                <p class="text-gray-600">Data pembayaran Anda telah tersimpan dengan kode konfirmasi:</p>
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 mt-3 inline-block">
                    <span class="font-mono text-lg font-bold text-blue-700">{{ $confirmation->confirmation_code }}</span>
                </div>
            </div>

            <!-- Summary Card -->
            <div class="card p-6 mb-8">
                <h3 class="text-xl font-semibold mb-6 flex items-center gap-3 font-poppins">
                    <span class="w-1 h-6 bg-gradient-to-b from-blue-500 to-green-500 rounded-full"></span>
                    Ringkasan Pembayaran
                </h3>

                <div class="space-y-4">
                    <div class="flex justify-between py-2 border-b border-gray-100">
                        <span class="text-gray-600">Nama</span>
                        <span class="font-medium">{{ $confirmation->payer_name }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gray-100">
                        <span class="text-gray-600">Kategori</span>
                        <span class="font-medium">{{ $confirmation->locationCategory->name }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gray-100">
                        <span class="text-gray-600">Lokasi</span>
                        <span class="font-medium">{{ $confirmation->location->name }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gray-100">
                        <span class="text-gray-600">Periode</span>
                        <span class="font-medium">
                            @php
                                $months = [
                                    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                                    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                                    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                                ];
                            @endphp
                            {{ $months[$confirmation->month] }} {{ $confirmation->year }}
                        </span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gray-100">
                        <span class="text-gray-600">Nominal</span>
                        <span class="font-medium text-green-600">Rp {{ number_format($confirmation->amount, 0, ',', '.') }}</span>
                    </div>
                    @if($confirmation->notes)
                    <div class="flex justify-between py-2 border-b border-gray-100">
                        <span class="text-gray-600">Catatan</span>
                        <span class="font-medium">{{ $confirmation->notes }}</span>
                    </div>
                    @endif
                    <div class="flex justify-between py-2">
                        <span class="text-gray-600">Status</span>
                        <span class="px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-sm font-medium">
                            {{ $confirmation->status_label }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="space-y-4">
                <button onclick="sendWhatsApp()" class="w-full btn btn-success py-4 text-base font-semibold flex items-center justify-center">
                    <i class="fab fa-whatsapp mr-2"></i>
                    Konfirmasi via WhatsApp
                </button>

                <a href="/" class="w-full btn btn-outline py-4 text-base font-semibold text-center block">
                    <i class="fas fa-home mr-2"></i>
                    Kembali ke Beranda
                </a>
            </div>
        </div>
    </div>

    <script>
        function sendWhatsApp() {
            const message = `{{ $whatsappMessage }}`;
            const whatsappUrl = `https://wa.me/6281313144088?text=${encodeURIComponent(message)}`;
            window.open(whatsappUrl, '_blank');
        }
    </script>

</body>
</html>
