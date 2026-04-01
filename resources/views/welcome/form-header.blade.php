<!-- Header -->
<header class="mb-6">
    <div class="flex items-center justify-between mb-4">
        <div class="flex items-center gap-3">
            <div class="logo-container p-3 bg-white rounded-2xl relative shadow-md">
                <div class="deco-circle deco-circle-1"></div>
                <div class="deco-circle deco-circle-2"></div>
                <img src="{{ asset('storage/rumio.png') }}" alt="Logo rumio" class="w-12 h-12 object-contain relative z-10">
            </div>
            <div>
                <h1 class="text-xl md:text-2xl font-bold font-quicksand" style="background: linear-gradient(135deg, #3b82f6, #10b981); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                    {{ $category->name }}
                </h1>
            </div>
        </div>
        <a href="/" class="flex items-center gap-2 px-3 py-2 bg-white rounded-full text-sm font-medium shadow-md hover:shadow-lg transition-all">
            <i class="fas fa-arrow-left"></i>
            <span class="hidden sm:inline">Kembali</span>
        </a>
    </div>
</header>
