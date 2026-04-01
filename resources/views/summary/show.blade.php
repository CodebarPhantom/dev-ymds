@extends('layouts.main')


@section('content')
    <!-- Container -->
    {{-- @include('backoffice.config.company.partials.submenu') --}}
    <!-- Container -->

    <div class="container-fixed">
        <div class="flex flex-wrap items-center lg:items-end justify-between gap-5 pb-2">
            <div class="flex flex-col justify-center gap-2">
                <h1 class="text-xl font-bold leading-none text-gray-900">
                    {{ $data['pageTitle'] }}
                </h1>
            </div>
            <div class="flex items-center gap-2.5">
                <div class="flex items-center gap-2.5">
                    <a class="btn text-center btn-sm btn-primary"
                        href="{{ route('summaries.index', $data['formData']['form_code']) }}">
                        <i class="ki-filled ki-left"></i></i>Kembali
                    </a>
                </div>
            </div>

        </div>
    </div>

    @include('partials.attention')

    <div class="container-fixed">
        <div class="grid gap-5 mx-auto">
            <!-- Card Gabungan: Approver & Total Kesalahan -->
            <div class="bg-gradient-to-r from-blue-500 to-indigo-600 rounded-xl shadow-lg p-5 text-white mb-6">
                <div class="flex flex-col md:flex-row justify-between items-center">
                    <div class="flex items-center mb-4 md:mb-0">
                        <div class="bg-white/20 backdrop-blur-sm rounded-full p-3 mr-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-lg font-bold">Evaluasi Diperiksa oleh</h2>
                            <p class="text-blue-100 text-sm">{{ $data['entryData']['header']['approver']['name'] }}</p>
                        </div>
                    </div>

                    <div class="flex items-center">
                        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 text-center">
                            <div class="text-3xl font-bold">
                                {{ collect($data['entryData']['sections'])->sum(function ($section) {
                                    return collect($section['questions'])->sum('answer');
                                }) }}
                            </div>
                            <div class="text-xs text-blue-200">Total Kesalahan</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card Detail Hafalan dengan Desain Modern -->
            <div class="bg-gradient-to-r from-emerald-500 to-teal-600 rounded-xl shadow-lg overflow-hidden">
                <!-- Header Card -->
                <div class="p-5 pb-0">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h2 class="text-xl font-bold text-white">Informasi Umum</h2>
                            {{-- <p class="text-emerald-100 text-sm">Informasi progress hafalan Quran</p> --}}
                        </div>
                        <div class="bg-white/20 backdrop-blur-sm rounded-full p-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Konten dengan Background Putih -->
                <div class="bg-white rounded-t-3xl p-5 mt-2">
                    <!-- Informasi Utama -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-5">
                        <!-- Santri & Tanggal -->
                        <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                            <div class="flex items-start">
                                <div class="bg-emerald-100 p-2 rounded-lg mr-3">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-emerald-600" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                </div>
                                <div>
                                    <div class="text-xs text-gray-500 mb-1">Peserta</div>
                                    <div class="font-semibold text-gray-800">
                                        {{ $data['entryData']['header']['user']['name'] }}</div>
                                    <div class="text-xs text-gray-500 mt-1">
                                        {{ $data['entryData']['header']['formatted_entry_date'] }}</div>
                                </div>
                            </div>
                        </div>

                        <!-- Surah, Halaman & Ayat -->
                        <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                            <div class="flex items-start">
                                <div class="bg-blue-100 p-2 rounded-lg mr-3">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                    </svg>
                                </div>
                                <div>
                                    <div class="text-xs text-gray-500 mb-1">Surah</div>
                                    <div class="font-semibold text-gray-800">
                                        {{ $data['entryData']['header']['surah']['name'] }}</div>
                                    <div class="text-xs text-gray-500 mt-1">
                                        {{ $data['entryData']['header']['surah']['name_latin'] }} •
                                        Hal. {{ $data['entryData']['header']['page'] }} •
                                        Ayat
                                        {{ $data['entryData']['header']['verse_start'] }}-{{ $data['entryData']['header']['verse_end'] }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-5">
                        <div class="border border-gray-200 rounded-xl p-4 hover:shadow-lg transition-shadow">
                            <div class="flex items-start gap-3">
                                <!-- Icon -->
                                <div class="bg-blue-100 p-2 rounded-lg">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>

                                <!-- Content -->
                                <div class="flex-1">
                                    <div class="text-xs text-gray-500 mb-1">Keterangan</div>
                                    <p class="text-sm text-gray-700">
                                        @php
                                            $kelasValue = $data['entryData']['header']['metadata']['kelas'] ?? null;
                                            $nilaiValue = $data['entryData']['header']['metadata']['nilai'] ?? null;

                                            $kelasMap = [
                                                'tamhidi1' => 'Tamhidi 1/2 hlm. pertama',
                                                'tamhidi2' => 'Tamhidi 1/2 hlm. kedua',
                                                'tajwidi' => 'Tajwidi 1 hlm.',
                                            ];

                                            $nilaiMap = [
                                                'lulus' => '✅ Lulus',
                                                'tidak_lulus' => '❌ Tidak Lulus',
                                            ];
                                        @endphp

                                        {{ $kelasMap[$kelasValue] ?? 'Kelas Tidak Dipilih' }}
                                        /
                                        {{ $nilaiMap[$nilaiValue] ?? '-' }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Catatan -->
                        <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                            <div class="flex items-start">
                                <div class="bg-amber-100 p-2 rounded-lg mr-3">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-amber-600" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <div class="text-xs text-gray-500 mb-1">Catatan</div>
                                    <p class="text-sm text-gray-700">
                                        {{ $data['entryData']['header']['notes'] ?: 'Tidak ada catatan' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Total kesalahan untuk semua kategori -->

            <!-- Visualisasi Progress dengan Desain Modern -->
            <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm mb-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-base font-semibold text-gray-800">Distribusi Kesalahan</h3>
                    <div class="flex space-x-2">
                        <div class="flex items-center">
                            <div class="w-3 h-3 rounded-full bg-green-500 mr-1"></div>
                            <span class="text-xs text-gray-500">Rendah</span>
                        </div>
                        <div class="flex items-center">
                            <div class="w-3 h-3 rounded-full bg-yellow-500 mr-1"></div>
                            <span class="text-xs text-gray-500">Sedang</span>
                        </div>
                        <div class="flex items-center">
                            <div class="w-3 h-3 rounded-full bg-red-500 mr-1"></div>
                            <span class="text-xs text-gray-500">Tinggi</span>
                        </div>
                    </div>
                </div>

                <div class="space-y-4">
                    @foreach ($data['entryData']['sections'] as $section)
                        @php
                            $sectionTotal = collect($section['questions'])->sum('answer');
                            $maxErrors = 15; // Asumsi maksimal kesalahan per kategori
                            $percentage = min(100, ($sectionTotal / $maxErrors) * 100);
                        @endphp
                        <div class="group">
                            <div class="flex justify-between text-sm mb-2">
                                <span
                                    class="font-medium text-gray-700 group-hover:text-blue-600 transition-colors">{{ $section['title'] }}</span>
                                <span
                                    class="font-medium {{ $sectionTotal > 10 ? 'text-red-600' : ($sectionTotal > 5 ? 'text-yellow-600' : 'text-green-600') }}">
                                    {{ $sectionTotal }} kesalahan
                                </span>
                            </div>
                            <div class="relative">
                                <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden">
                                    <div class="h-3 rounded-full transition-all duration-500 ease-out
                            {{ $sectionTotal > 10 ? 'bg-red-500' : ($sectionTotal > 5 ? 'bg-yellow-500' : 'bg-green-500') }}"
                                        style="width: {{ $percentage }}%">
                                        <div
                                            class="absolute right-0 top-0 bottom-0 w-4 bg-gradient-to-l from-white/30 to-transparent">
                                        </div>
                                    </div>
                                </div>
                                <div class="flex justify-between text-xs text-gray-500 mt-1">
                                    <span>0</span>
                                    <span>{{ round($percentage) }}%</span>
                                    <span>{{ $maxErrors }}</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>




            @foreach ($data['entryData']['sections'] as $section)
                <!-- Card untuk setiap section -->
                <div
                    class="mb-5 bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm hover:shadow-md transition-shadow duration-200">
                    <!-- Header section dengan total kesalahan per kategori -->
                    <div class="px-4 py-3 bg-gray-50 border-b border-gray-200">
                        <div class="flex justify-between items-center">
                            <h3 class="text-base font-semibold text-gray-800">{{ $section['title'] }}</h3>
                            <div class="flex items-center">
                                <span class="text-xs text-gray-500 mr-2">Total:</span>
                                @php
                                    $sectionTotal = collect($section['questions'])->sum('answer');
                                @endphp
                                <span
                                    class="inline-flex items-center justify-center px-2.5 py-0.5 rounded-full text-xs font-medium
                        {{ $sectionTotal > 10
                            ? 'bg-red-100 text-red-800'
                            : ($sectionTotal > 5
                                ? 'bg-yellow-100 text-yellow-800'
                                : 'bg-green-100 text-green-800') }}">
                                    {{ $sectionTotal }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Konten pertanyaan dan jawaban -->
                    <div class="p-3">
                        <div class="grid grid-cols-1 gap-2">
                            @foreach ($section['questions'] as $question)
                                <div
                                    class="flex items-center justify-between p-2 rounded-lg
                        {{ $loop->even ? 'bg-gray-50' : 'bg-white' }}
                        hover:bg-blue-50 transition-colors duration-150">
                                    <div class="flex-1 pr-3">
                                        <p class="text-sm text-gray-700 truncate">{{ $question['text'] }}</p>
                                    </div>
                                    <div class="flex-shrink-0">
                                        <div class="flex items-center">
                                            @if ($question['answer'] > 0)
                                                <div
                                                    class="w-8 h-8 rounded-full flex items-center justify-center
                                        {{ $question['answer'] > 5
                                            ? 'bg-red-100 text-red-800'
                                            : ($question['answer'] > 2
                                                ? 'bg-yellow-100 text-yellow-800'
                                                : 'bg-green-100 text-green-800') }}">
                                                    <span class="text-sm font-bold">{{ $question['answer'] }}</span>
                                                </div>
                                            @else
                                                <div
                                                    class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center">
                                                    <span class="text-sm font-bold text-gray-500">0</span>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach


        </div>
    </div>

    <!-- End of Container -->
@endsection

@push('javascript')
@endpush
