<!-- Header -->
<header class="text-center mb-12">
    <div class="logo-container inline-block p-6 bg-white rounded-3xl mb-6 relative shadow-lg">
        <div class="deco-circle deco-circle-1"></div>
        <div class="deco-circle deco-circle-2"></div>
        <img src="{{ asset('storage/rumio.png') }}" alt="Logo rumio" class="w-24 h-24 object-contain relative z-10">
    </div>
    <h1 class="text-5xl md:text-6xl font-bold font-quicksand mb-2" style="background: linear-gradient(135deg, #3b82f6, #10b981); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
        rumio
    </h1>
    <div class="inline-flex items-center gap-2 px-6 py-3 bg-white rounded-full text-sm font-medium shadow-md mb-6">
        <div class="w-3 h-3 bg-green-400 rounded-full animate-pulse"></div>
        <span>Simple • Organized • Transparent</span>
    </div>

    <!-- Tracking Search -->
    <div class="max-w-2xl mx-auto">
        <form action="{{ route('payment.tracking') }}" method="GET" class="flex flex-col sm:flex-row gap-3">
            <input type="text" name="code" class="flex-1 border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Masukkan kode (contoh: PAY-ABC12345)">
            <button type="submit" class="btn btn-primary px-6 py-3 text-sm font-semibold whitespace-nowrap">
                <i class="fas fa-search mr-2"></i>
                <span class="hidden sm:inline">Cek Status</span>
                <span class="sm:hidden">Cek Status Pembayaran</span>
            </button>
        </form>
    </div>
</header>
