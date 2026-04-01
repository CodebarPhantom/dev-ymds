<!-- Categories Section -->
@php
    $blockCategories = $categories->where('type', 'block');
    $socialCategories = $categories->where('type', 'social');
    $otherCategories = $categories->where('type', 'other');
@endphp

@if ($categories->count() > 0)
    <!-- Block Categories -->
    @if ($blockCategories->count() > 0)
        <div class="mb-8 animate-fade-up" style="animation-delay: 0.1s">
            <h2 class="text-2xl font-bold mb-6 flex items-center gap-3 font-poppins">
                <span class="w-1 h-8 bg-gradient-to-b from-blue-500 to-green-500 rounded-full"></span>
                Pilih Blok Anda
            </h2>
            <div class="grid grid-cols-2 gap-4">
                @foreach ($blockCategories as $index => $category)
                    <a href="{{ route('landing.filter', ['categoryId' => $category->id]) }}"
                        class="card p-6 cursor-pointer {{ $loop->last && $blockCategories->count() % 2 == 1 ? 'col-span-2' : '' }}"
                        style="animation-delay: {{ $index * 0.1 + 0.2 }}s">
                        <div class="flex flex-col items-center">
                            <div class="icon-container mb-4"
                                @if ($category->color_class) style="background: {{ $category->color_class }}"
                                 @else
                                 style="background: linear-gradient(135deg, #3b82f6, #10b981)" @endif>
                                @if ($category->url_icon)
                                    <img src="{{ asset($category->url_icon) }}" alt="{{ $category->name }}"
                                        class="w-6 h-6 object-contain">
                                @else
                                    <i class="fas {{ str_replace('bi-', 'fa-', $category->icon_class ?? 'fa-home') }}"></i>
                                @endif
                            </div>
                            <h3 class="text-lg font-semibold mb-1 font-quicksand">{{ $category->name }}</h3>
                            @if ($category->description)
                                <p class="text-sm text-gray-500">{{ $category->description }}</p>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Social Categories -->
    @if ($socialCategories->count() > 0)
        <div class="mb-8 animate-fade-up" style="animation-delay: 0.3s">
            <h2 class="text-2xl font-bold mb-6 flex items-center gap-3 font-poppins">
                <span class="w-1 h-8 bg-gradient-to-b from-purple-500 to-pink-500 rounded-full"></span>
                Kegiatan Sosial
            </h2>
            <div class="grid grid-cols-2 gap-4">
                @foreach ($socialCategories as $index => $category)
                    <a href="{{ route('landing.filter', ['categoryId' => $category->id]) }}"
                        class="card p-6 cursor-pointer {{ $loop->last && $socialCategories->count() % 2 == 1 ? 'col-span-2' : '' }}"
                        style="animation-delay: {{ $index * 0.1 + 0.4 }}s">
                        <div class="flex flex-col items-center">
                            <div class="icon-container mb-4"
                                @if ($category->color_class) style="background: {{ $category->color_class }}"
                                 @else
                                 style="background: linear-gradient(135deg, #8b5cf6, #ec4899)" @endif>
                                @if ($category->url_icon)
                                    <img src="{{ asset($category->url_icon) }}" alt="{{ $category->name }}"
                                        class="w-6 h-6 object-contain">
                                @else
                                    <i class="fas {{ str_replace('bi-', 'fa-', $category->icon_class ?? 'fa-users') }}"></i>
                                @endif
                            </div>
                            <h3 class="text-lg font-semibold mb-1 font-quicksand">{{ $category->name }}</h3>
                            @if ($category->description)
                                <p class="text-sm text-gray-500">{{ $category->description }}</p>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Other Categories -->
    @if ($otherCategories->count() > 0)
        <div class="mb-8 animate-fade-up" style="animation-delay: 0.5s">
            <h2 class="text-2xl font-bold mb-6 flex items-center gap-3 font-poppins">
                <span class="w-1 h-8 bg-gradient-to-b from-orange-500 to-red-500 rounded-full"></span>
                Lainnya
            </h2>
            <div class="grid grid-cols-2 gap-4">
                @foreach ($otherCategories as $index => $category)
                    <a href="{{ route('landing.filter', ['categoryId' => $category->id]) }}"
                        class="card p-6 cursor-pointer {{ $loop->last && $otherCategories->count() % 2 == 1 ? 'col-span-2' : '' }}"
                        style="animation-delay: {{ $index * 0.1 + 0.6 }}s">
                        <div class="flex flex-col items-center">
                            <div class="icon-container mb-4"
                                @if ($category->color_class) style="background: {{ $category->color_class }}"
                                 @else
                                 style="background: linear-gradient(135deg, #f59e0b, #ef4444)" @endif>
                                @if ($category->url_icon)
                                    <img src="{{ asset($category->url_icon) }}" alt="{{ $category->name }}"
                                        class="w-6 h-6 object-contain">
                                @else
                                    <i class="fas {{ str_replace('bi-', 'fa-', $category->icon_class ?? 'fa-ellipsis-h') }}"></i>
                                @endif
                            </div>
                            <h3 class="text-lg font-semibold mb-1 font-quicksand">{{ $category->name }}</h3>
                            @if ($category->description)
                                <p class="text-sm text-gray-500">{{ $category->description }}</p>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    @endif
@else
    <div class="bg-white rounded-xl p-6 text-center shadow-lg mb-8 animate-fade-up">
        <i class="bi bi-exclamation-circle text-5xl text-gray-400 mb-3"></i>
        <p class="text-gray-600 font-medium">Kategori belum tersedia</p>
    </div>
@endif


