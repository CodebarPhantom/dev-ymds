<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Tracking Pembayaran - RT 037</title>
    <meta name="description" content="Tracking status pembayaran RT.037/RW.014 Villa Permata Hijau">

    <link rel="icon" type="image/png" href="{{ asset('rumio.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&family=Quicksand:wght@500;600;700&display=swap"
        rel="stylesheet">
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
            <!-- Include Header -->
            <!-- Header -->
            <header class="text-center mb-12">
                <div class="logo-container inline-block p-6 bg-white rounded-3xl mb-6 relative shadow-lg">
                    <div class="deco-circle deco-circle-1"></div>
                    <div class="deco-circle deco-circle-2"></div>
                    <img src="{{ asset('storage/rumio.png') }}" alt="Logo rumio"
                        class="w-24 h-24 object-contain relative z-10">
                </div>
                <h1 class="text-5xl md:text-6xl font-bold font-quicksand mb-2"
                    style="background: linear-gradient(135deg, #3b82f6, #10b981); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                    rumio
                </h1>
                <div
                    class="inline-flex items-center gap-2 px-6 py-3 bg-white rounded-full text-sm font-medium shadow-md mb-6">
                    <div class="w-3 h-3 bg-green-400 rounded-full animate-pulse"></div>
                    <span>Simple • Organized • Transparent</span>
                </div>
            </header>


            <!-- Search Card -->
            <div class="card p-6 mb-8">
                <h3 class="text-xl font-semibold mb-6 flex items-center gap-3 font-poppins">
                    <span class="w-1 h-6 bg-gradient-to-b from-blue-500 to-green-500 rounded-full"></span>
                    Cari Status Pembayaran
                </h3>

                <form id="trackingForm" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Kode Konfirmasi</label>
                        <input type="text" id="confirmationCode" value="{{ $code ?? '' }}"
                            class="w-full border border-gray-300 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            placeholder="Masukkan kode konfirmasi (contoh: PAY-ABC12345)">
                    </div>

                    <button type="submit" class="w-full btn btn-primary py-3 text-base font-semibold">
                        <i class="fas fa-search mr-2"></i>
                        Cari Status Pembayaran
                    </button>
                </form>
            </div>

            <!-- Result Card (Hidden by default) -->
            <div id="resultCard" class="card p-6 mb-8 hidden">
                <h3 class="text-xl font-semibold mb-6 flex items-center gap-3 font-poppins">
                    <span class="w-1 h-6 bg-gradient-to-b from-green-500 to-blue-500 rounded-full"></span>
                    Detail Pembayaran
                </h3>

                <div id="resultContent" class="space-y-4">
                    <!-- Content will be populated by JavaScript -->
                </div>
            </div>

            <!-- Error Card (Hidden by default) -->
            <div id="errorCard" class="card p-6 mb-8 hidden bg-red-50 border border-red-200">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-exclamation-triangle text-red-600"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-red-800">Data Tidak Ditemukan</h3>
                        <p class="text-sm text-red-600">Kode konfirmasi yang Anda masukkan tidak valid atau tidak
                            ditemukan.</p>
                    </div>
                </div>
            </div>

            <!-- Back Button -->
            <div class="text-center">
                <a href="/" class="btn btn-outline px-6 py-3 text-base font-semibold">
                    <i class="fas fa-home mr-2"></i>
                    Kembali ke Beranda
                </a>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-search if code is provided
            const confirmationCode = document.getElementById('confirmationCode').value;
            if (confirmationCode) {
                document.getElementById('trackingForm').dispatchEvent(new Event('submit'));
            }
        });

        document.getElementById('trackingForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const confirmationCode = document.getElementById('confirmationCode').value.trim();
            if (!confirmationCode) {
                alert('Mohon masukkan kode konfirmasi');
                return;
            }

            // Hide previous results
            document.getElementById('resultCard').classList.add('hidden');
            document.getElementById('errorCard').classList.add('hidden');

            // Search for payment confirmation
            console.log('Searching for code:', confirmationCode);
            fetch(`/api/v1/payment/track/${confirmationCode}`)
                .then(response => response.json())
                .then(data => {
                    console.log('API Response:', data);
                    if (data.data && !data.error) {
                        showResult(data.data);
                    } else {
                        console.log('No data found or error=true');
                        showError();
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showError();
                });
        });

        function showResult(confirmation) {
            const months = [
                '', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
            ];

            const statusClass = confirmation.status === 'sudah_dicek' ? 'bg-green-100 text-green-800' :
                'bg-yellow-100 text-yellow-800';

            const resultContent = `
                <div class="flex justify-between py-2 border-b border-gray-100">
                    <span class="text-gray-600">Kode Konfirmasi</span>
                    <span class="font-medium font-mono">${confirmation.confirmation_code}</span>
                </div>
                <div class="flex justify-between py-2 border-b border-gray-100">
                    <span class="text-gray-600">Nama</span>
                    <span class="font-medium">${confirmation.payer_name}</span>
                </div>
                <div class="flex justify-between py-2 border-b border-gray-100">
                    <span class="text-gray-600">Kategori</span>
                    <span class="font-medium">${confirmation.category}</span>
                </div>
                <div class="flex justify-between py-2 border-b border-gray-100">
                    <span class="text-gray-600">Lokasi</span>
                    <span class="font-medium">${confirmation.location}</span>
                </div>
                <div class="flex justify-between py-2 border-b border-gray-100">
                    <span class="text-gray-600">Periode</span>
                    <span class="font-medium">${months[confirmation.month]} ${confirmation.year}</span>
                </div>
                <div class="flex justify-between py-2 border-b border-gray-100">
                    <span class="text-gray-600">Nominal</span>
                    <span class="font-medium text-green-600">Rp ${confirmation.amount_formatted}</span>
                </div>
                <div class="flex justify-between py-2 border-b border-gray-100">
                    <span class="text-gray-600">Tanggal Submit</span>
                    <span class="font-medium">${confirmation.created_at}</span>
                </div>
                <div class="flex justify-between py-2">
                    <span class="text-gray-600">Status</span>
                    <span class="px-3 py-1 ${statusClass} rounded-full text-sm font-medium">
                        ${confirmation.status_label}
                    </span>
                </div>
            `;

            document.getElementById('resultContent').innerHTML = resultContent;
            document.getElementById('resultCard').classList.remove('hidden');
        }

        function showError() {
            document.getElementById('errorCard').classList.remove('hidden');
        }
    </script>

</body>

</html>
