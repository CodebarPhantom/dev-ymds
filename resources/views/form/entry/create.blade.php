@extends('layouts.main')


@section('content')
    <!-- Container -->
    {{-- @include('backoffice.config.company.partials.submenu') --}}
    <!-- Container -->
    <form id="tahsin-form" action="{{ route('forms.store.tahsin-tilawah') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('POST')
        <input type="hidden" name="form_id" value="{{ $data['formData']['id'] }}">
        <div class="container-fixed">
            <div class="flex flex-wrap items-center lg:items-end justify-between gap-5 pb-2">
                <div class="flex flex-col justify-center gap-2">
                    <h1 class="text-xl font-bold leading-none text-gray-900">
                        {{ $data['pageTitle'] }}
                    </h1>
                </div>
                <div class="flex items-center gap-2.5">
                    <div class="flex items-center gap-2.5">
                        <a class="btn text-center btn-sm btn-primary" href="{{ route('users.index') }}">
                            <i class="ki-filled ki-left"></i></i>Kembali
                        </a>
                    </div>
                    <div class="flex items-center gap-2.5">
                        <button id="submit-button" type="submit" class="btn btn-sm text-center btn-success">
                            <i class="ki-filled ki-check"></i>{{ $data['pageTitle'] }}
                        </button>
                    </div>
                </div>

            </div>
        </div>

        @include('partials.attention')

        <div class="container-fixed">
            <div class="grid gap-5 mx-auto">
                <div class="card pb-2">
                    <div class="card-body grid gap-4">

                        <!-- Baris 2: Tanggal (1 kolom penuh) -->
                        <div class="flex flex-col gap-1">
                            <label class="form-label text-sm">
                                Tanggal
                                <span class="text-danger"> *</span>
                            </label>
                            <input class="input w-full px-3 py-1.5 text-sm" name="entry_date" type="date"
                                value="{{ old('entry_date', $data['user']['entry_date'] ?? date('Y-m-d')) }}" />
                        </div>

                        @if ($data['multiLocation'])
                            <div class="flex flex-col gap-4">
                                <label class="form-label text-sm">
                                    Lokasi Tahsin
                                    <span class="text-danger"> *</span>
                                </label>
                                <div class="relative combobox" data-options='@json($data['locations'])'
                                    data-multiple="false">
                                    <div
                                        class="pill-container flex items-center w-full px-2 py-1.5 border border-gray-300 rounded-md bg-white focus:ring-2 focus:ring-blue-500">
                                        <input type="text" placeholder="Cari Lokasi Tahsin"
                                            class="search-box flex-grow px-2 py-1 text-sm text-gray-700 bg-transparent border-none outline-none" />
                                    </div>
                                    <input id="location_id" type="hidden" class="selected-data" name="location_id" />
                                    <div
                                        class="dropdown-menu absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg hidden">
                                        <div class="options-container max-h-40 overflow-y-auto"></div>
                                    </div>
                                </div>
                            </div>
                        @endif


                        <!-- Baris 1: Nama & Surah (2 kolom) -->
                        <div class="grid grid-cols-2 gap-4">
                            <!-- Nama -->
                            @if ($data['multiLocation'])
                                <div class="flex flex-col gap-1">
                                    <label class="form-label text-sm">
                                        Nama
                                        <span class="text-danger"> *</span>
                                    </label>
                                    <div class="relative combobox" id="location_combobox"
                                        data-api="{{ route('api.v1.users.get-combobox') }}" data-collection="locations"
                                        data-params='{"location_id": ""}' data-multiple="false">
                                        <div
                                            class="pill-container flex items-center w-full px-2 py-1.5 border border-gray-300 rounded-md bg-white focus:ring-2 focus:ring-blue-500">
                                            <input type="text" placeholder="Cari Peserta"
                                                class="search-box flex-grow px-2 py-1 text-sm text-gray-700 bg-transparent border-none outline-none" />
                                        </div>
                                        <input id="user_id" type="hidden" class="selected-data" name="user_id" />
                                        <div
                                            class="dropdown-menu absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg hidden">
                                            <div class="options-container max-h-40 overflow-y-auto"></div>
                                        </div>
                                    </div>


                                </div>
                            @else
                                <div class="flex flex-col gap-1">
                                    <label class="form-label text-sm">
                                        Nama
                                        <span class="text-danger"> *</span>
                                    </label>
                                    <div class="relative combobox" data-options='@json($data['users'])'
                                        data-multiple="false">
                                        <div
                                            class="pill-container flex items-center w-full px-2 py-1.5 border border-gray-300 rounded-md bg-white focus:ring-2 focus:ring-blue-500">
                                            <input type="text" placeholder="Cari Peserta"
                                                class="search-box flex-grow px-2 py-1 text-sm text-gray-700 bg-transparent border-none outline-none" />
                                        </div>
                                        <input id="user_id" type="hidden" class="selected-data" name="user_id" />
                                        <div
                                            class="dropdown-menu absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg hidden">
                                            <div class="options-container max-h-40 overflow-y-auto"></div>
                                        </div>
                                    </div>


                                </div>
                            @endif


                            <!-- Surah -->
                            <div class="flex flex-col gap-1">
                                <label class="form-label text-sm">
                                    Surah
                                    <span class="text-danger"> *</span>
                                </label>
                                <div class="relative combobox" data-options='@json($data['surahs'])'
                                    data-multiple="false">
                                    <div
                                        class="pill-container flex items-center w-full px-2 py-1.5 border border-gray-300 rounded-md bg-white focus:ring-2 focus:ring-blue-500">
                                        <input type="text" placeholder="Cari Surat"
                                            class="search-box flex-grow px-2 py-1 text-sm text-gray-700 bg-transparent border-none outline-none" />
                                    </div>
                                    <input id="surah_id" type="hidden" class="selected-data" name="surah_id" />
                                    <div
                                        class="dropdown-menu absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg hidden">
                                        <div class="options-container max-h-40 overflow-y-auto"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Baris 3: Halaman, Ayat Dari, Ayat Sampai (3 kolom) -->
                        <div class="grid grid-cols-3 gap-4">
                            <!-- Halaman -->
                            <div class="flex flex-col gap-1">
                                <label class="form-label text-sm">
                                    Halaman
                                    <span class="text-danger"> *</span>
                                </label>
                                <input class="input w-full px-3 py-1.5 text-sm" name="page" type="number"
                                    min="1" value="{{ old('page', $data['user']['page'] ?? '') }}" />
                            </div>

                            <!-- Ayat Dari -->
                            <div class="flex flex-col gap-1">
                                <label class="form-label text-sm">
                                    Dari Ayat
                                    <span class="text-danger"> *</span>
                                </label>
                                <input class="input w-full px-3 py-1.5 text-sm" name="verse_start" type="number"
                                    min="1" value="{{ old('verse_start', $data['user']['verse_start'] ?? '') }}" />
                            </div>

                            <!-- Ayat Sampai -->
                            <div class="flex flex-col gap-1">
                                <label class="form-label text-sm">
                                    Sampai Ayat
                                    <span class="text-danger"> *</span>
                                </label>
                                <input class="input w-full px-3 py-1.5 text-sm" name="verse_end" type="number"
                                    min="1" value="{{ old('verse_end', $data['user']['verse_end'] ?? '') }}" />
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <!-- Grup Radio Kelas (Inline) -->
                            <div class="flex flex-col gap-3">
                                <label class="form-label text-sm">Kelas</label>
                                <label class="form-label flex items-center gap-2.5 text-nowrap">
                                    <input class="radio" name="kelas" type="radio" value="tamhidi1" />
                                    Tamhidi 1/2 hlm. pertama
                                </label>
                                <label class="form-label flex items-center gap-2.5 text-nowrap">
                                    <input class="radio" name="kelas" type="radio" value="tamhidi2" />
                                    Tamhidi 1/2 hlm. kedua
                                </label>
                                <label class="form-label flex items-center gap-2.5 text-nowrap">
                                    <input class="radio" name="kelas" type="radio" value="tajwidi" />
                                    Tajwidi 1 hlm.
                                </label>
                            </div>
                        </div>

                        <div class="flex flex-col gap-4">
                            <label class="form-label text-sm">Catatan</label>
                            <textarea class="textarea w-full px-3 py-1.5 text-sm" name="notes" placeholder="Catatan Tambahan" rows="2"></textarea>
                        </div>
                        <div class="flex flex-col gap-4">
                            <label class="form-label text-sm">Nilai</label>
                            <div class="flex gap-12">
                                <label class="form-label flex items-center gap-2.5 text-nowrap">
                                    <input class="radio radio-lg" name="nilai" type="radio" value="tidak_lulus" />
                                    Tidak Lulus
                                </label>
                                <label class="form-label flex items-center gap-2.5 text-nowrap">
                                    <input class="radio radio-lg" name="nilai" type="radio" value="lulus" />
                                    Lulus
                                </label>
                            </div>
                        </div>


                    </div>
                </div>
                <div class="card">
                    @foreach ($data['formData']['sections'] as $section)
                        <details class="border border-gray-200 rounded-lg mb-3 overflow-hidden">
                            <summary
                                class="cursor-pointer bg-gray-50 px-4 py-3 font-semibold text-gray-800 flex justify-between items-center">
                                <span>{{ $section['title'] }}</span>
                                <svg class="w-5 h-5 text-gray-600 transition-transform duration-300"
                                    xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7" />
                                </svg>
                            </summary>
                            <div class="p-3 bg-white">
                                @foreach ($section['questions'] as $question)
                                    <div
                                        class="flex items-start justify-between py-2 border-b border-gray-100 last:border-0">
                                        <!-- Teks pertanyaan di kiri -->
                                        <div class="flex-1 pr-4 min-w-0">
                                            <span class="text-gray-700 text-sm">{{ $question['text'] }}</span>
                                        </div>

                                        <!-- Kontrol di kanan -->
                                        <div class="flex items-center gap-2 flex-shrink-0">
                                            <button type="button"
                                                class="w-8 h-8 flex items-center justify-center btn-warning text-white rounded-full hover:bg-yellow-600 active:bg-yellow-700 transition-colors decrement-btn text-lg font-bold"
                                                data-question-id="{{ $question['question_id'] }}">
                                                -
                                            </button>
                                            <input type="number" min="0" step="1"
                                                class="w-10 h-8 flex items-center justify-center text-sm font-bold text-gray-700 count-input bg-gray-100 rounded-lg text-center [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none"
                                                data-question-id="{{ $question['question_id'] }}" value="0" />
                                            <button type="button"
                                                class="w-8 h-8 flex items-center justify-center btn-success text-white rounded-full hover:bg-green-600 active:bg-green-700 transition-colors increment-btn text-lg font-bold"
                                                data-question-id="{{ $question['question_id'] }}">
                                                +
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </details>
                    @endforeach
                </div>
            </div>
        </div>
    </form>
    <!-- End of Container -->
@endsection



@include('partials.advanced-selectbox')
@push('javascript')
@endpush

@push('javascript')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            setupLinkedCombobox('.combobox[data-options]', '#location_combobox', 'location_id');


            // Initialize counts object
            const counts = {};

            // Get all count inputs and initialize counts
            document.querySelectorAll('.count-input').forEach(input => {
                const questionId = input.dataset.questionId;
                counts[questionId] = 0;
                input.value = 0;

                // Add event listener for manual input changes
                input.addEventListener('change', function() {
                    const value = parseInt(this.value) || 0;
                    // Ensure value is not negative
                    if (value < 0) {
                        this.value = 0;
                        counts[questionId] = 0;
                    } else {
                        counts[questionId] = value;
                    }
                });
            });

            // Handle increment buttons
            document.querySelectorAll('.increment-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const questionId = this.dataset.questionId;
                    counts[questionId]++;
                    updateCountInput(questionId);

                    // Add animation effect
                    this.classList.add('scale-90');
                    setTimeout(() => {
                        this.classList.remove('scale-90');
                    }, 100);
                });
            });

            // Handle decrement buttons
            document.querySelectorAll('.decrement-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const questionId = this.dataset.questionId;
                    if (counts[questionId] > 0) {
                        counts[questionId]--;
                        updateCountInput(questionId);

                        // Add animation effect
                        this.classList.add('scale-90');
                        setTimeout(() => {
                            this.classList.remove('scale-90');
                        }, 100);
                    }
                });
            });

            // Update count input
            function updateCountInput(questionId) {
                const input = document.querySelector(`.count-input[data-question-id="${questionId}"]`);
                if (input) {
                    input.value = counts[questionId];

                    // Add animation effect
                    input.classList.add('scale-110');
                    setTimeout(() => {
                        input.classList.remove('scale-110');
                    }, 200);
                }
            }

            // Handle form submission
            document.getElementById('tahsin-form').addEventListener('submit', function(e) {
                e.preventDefault();

                // Get form data
                const formData = new FormData(this);
                const data = {};

                // Convert FormData to object
                for (let [key, value] of formData.entries()) {
                    data[key] = value;
                }

                // Add counts data
                data.counts = counts;

                // Send data using Axios
                axios.post(this.action, data, {
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    })
                    .then(response => {
                        // Handle success
                        console.log('Success:', response.data.data);
                        const successMessage = 'Data berhasil disimpan!';
                        alert(successMessage);
                        // Redirect or show success message
                        window.location.href = response.data.data.redirect || '{{ route('index') }}';
                    })
                    .catch(error => {
                        // Handle error
                        console.error('Error:', error.response.data);
                        // Show error messages
                        if (error.response.data.errors) {
                            // Display validation errors
                            let errorMessage = 'Validation errors:\n';
                            for (let field in error.response.data.errors) {
                                errorMessage +=
                                    `${field}: ${error.response.data.errors[field].join(', ')}\n`;
                            }
                            alert(errorMessage);
                        } else {
                            alert('An error occurred while submitting the form.');
                        }
                    });
            });
        });
    </script>
@endpush
