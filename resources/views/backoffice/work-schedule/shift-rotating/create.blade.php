@extends('layouts.main')

@section('content')
    <!-- Container -->
    @include('backoffice.work-schedule.partials.submenu')
    <!-- Container -->
    {{-- <form action="{{ route('work-schedule.shift-rotating.store') }}" method="post">
        @csrf --}}
    <div class="container-fixed">
        <div class="flex flex-wrap items-center lg:items-end justify-between gap-5 pb-7.5">
            <div class="flex flex-col justify-center gap-2">
                <h1 class="text-xl font-bold leading-none text-gray-900">
                    {{ $data['pageTitle'] }}
                </h1>
            </div>
            <div class="flex items-center gap-2.5">
                <div class="flex items-center gap-2.5">
                    <a class="btn text-center btn-sm btn-primary" href="{{ route('work-schedule.shift-rotating.index') }}">
                        <i class="ki-filled ki-left"></i></i>Kembali
                    </a>
                </div>
                <div class="flex items-center gap-2.5">

                    <button id="addShiftCard" class="btn text-center btn-sm btn-info"
                        href="{{ route('work-schedule.shift-rotating.index') }}">
                        <i class="ki-filled ki-notepad"></i>Tambah Baris
                    </button>
                </div>
                <div class="flex items-center gap-2.5">

                    <button id="submitShiftForm" type="button" class="btn btn-sm text-center btn-success">
                        <i class="ki-filled ki-check"></i>{{ $data['pageTitle'] }}
                    </button>
                </div>
            </div>

        </div>
    </div>

    @include('partials.attention')

    <div class="container-fixed">
        <div id="shiftCardsContainer" class="grid gap-5 mx-auto">
            {{-- <div class="card pb-2.5">
                <div class="card-body grid gap-5">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                            <label class="form-label max-w-56">
                                Kategori Jadwal
                            </label>
                            <div class="flex gap-12">
                                <label class="form-label flex items-center gap-2.5 text-nowrap">
                                    <input checked="true" class="radio " name="is_dayoff" type="radio" value="0" />
                                    Jadwal Masuk
                                </label>
                                <label class="form-label flex items-center gap-2.5 text-nowrap ">
                                    <input class="radio" name="is_dayoff" type="radio" value="1" />
                                    Jadwal Libur
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                            <label class="form-label max-w-56">
                                Dari Tanggal
                            </label>
                            <input class="input" name="start_date" placeholder="mm/dd/yyyy" type="date"
                                value="{{ old('start_date') }}" />
                        </div>
                        <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                            <label class="form-label max-w-56">
                                Sampai Tanggal
                            </label>
                            <input class="input" name="end_date" placeholder="mm/dd/yyyy" type="date"
                                value="{{ old('end_date') }}" />
                        </div>
                    </div>


                    <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                        <label class="form-label max-w-56">Karyawan Shift</label>
                        <div class="relative inline-block w-full combobox"
                            data-api="{{ route('api.v1.workforce.employee.get-all') }}" data-collection="employees"
                            data-multiple="true">
                            <div
                                class="pill-container flex flex-wrap items-center w-full px-2 py-2 border border-gray-300 rounded-md bg-white focus:ring-2 focus:ring-blue-500">
                                <input type="text" placeholder="Search..."
                                    class="search-box flex-grow px-2 py-1 text-sm text-gray-700 bg-transparent border-none outline-none" />
                            </div>
                            <input type="hidden" class="selected-data" name="shift_employees" value="" />
                            <div
                                class="dropdown-menu absolute z-10 w-full mt-2 bg-white border border-gray-300 rounded-md shadow-lg hidden">
                                <div class="options-container max-h-40 overflow-y-auto"></div>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                        <label class="form-label max-w-56">
                            Shift
                        </label>
                        <select class="select" name="shift_id" required>
                            <option value="" selected>--- Silahkan Pilih ---</option>
                            @foreach ($data['shifts'] as $shift)
                                <option value="{{ $shift->id }}" {{ old('shift_id') == $shift->id ? 'selected' : '' }}>
                                    {{ $shift->name }} {{ $shift->formatted_start_time }} -
                                    {{ $shift->formatted_end_time }}
                                </option>
                            @endforeach
                        </select>
                    </div>


                </div>
            </div> --}}
        </div>
    </div>
    {{-- </form> --}}

    <!-- End of Container -->
@endsection
@include('partials.advanced-selectbox')
@push('javascript')
    <script>
        let shiftCardIndex = 0; // Track the number of cards

        document.getElementById('addShiftCard').addEventListener('click', function(event) {

            shiftCardIndex++;

            const shiftCard = document.createElement('div');
            shiftCard.className = "card pb-2.5";
            shiftCard.innerHTML = `
                <div class="card-body grid gap-5 relative">
                    <button type="button" class="absolute top-2 right-2 text-red-500 removeShiftCard">✖</button>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                            <label class="form-label max-w-56">Kategori Jadwal</label>
                            <div class="flex gap-12">
                                <label class="form-label flex items-center gap-2.5 text-nowrap">
                                    <input checked class="radio" name="shift_cards[${shiftCardIndex}][is_dayoff]" type="radio" value="0" />
                                    Jadwal Masuk
                                </label>
                                <label class="form-label flex items-center gap-2.5 text-nowrap">
                                    <input class="radio" name="shift_cards[${shiftCardIndex}][is_dayoff]" type="radio" value="1" />
                                    Jadwal Libur
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                            <label class="form-label max-w-56">Dari Tanggal</label>
                            <input class="input" name="shift_cards[${shiftCardIndex}][start_date]" type="date" placeholder="mm/dd/yyyy" />
                        </div>
                        <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                            <label class="form-label max-w-56">Sampai Tanggal</label>
                            <input class="input" name="shift_cards[${shiftCardIndex}][end_date]" type="date" placeholder="mm/dd/yyyy" />
                        </div>
                    </div>

                    <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                        <label class="form-label max-w-56">Karyawan Shift</label>
                        <div class="relative inline-block w-full combobox"
                            data-api="{{ route('api.v1.workforce.employee.get-all') }}"
                            data-collection="employees"
                            data-multiple="true">
                            <div class="pill-container flex flex-wrap items-center w-full px-2 py-2 border border-gray-300 rounded-md bg-white focus:ring-2 focus:ring-blue-500">
                                <input type="text" placeholder="Search..." class="search-box flex-grow px-2 py-1 text-sm text-gray-700 bg-transparent border-none outline-none" />
                            </div>
                            <input type="hidden" class="selected-data" name="shift_cards[${shiftCardIndex}][shift_employees]" value="" />
                            <div class="dropdown-menu absolute z-10 w-full mt-2 bg-white border border-gray-300 rounded-md shadow-lg hidden">
                                <div class="options-container max-h-40 overflow-y-auto"></div>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                        <label class="form-label max-w-56">Shift</label>
                        <select class="select" name="shift_cards[${shiftCardIndex}][shift_id]" required>
                            <option value="" selected>--- Silahkan Pilih ---</option>
                            @foreach ($data['shifts'] as $shift)
                                <option value="{{ $shift->id }}">{{ $shift->name }} ({{ $shift->formatted_start_time }} - {{ $shift->formatted_end_time }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            `;

            document.getElementById('shiftCardsContainer').appendChild(shiftCard);

            // Reinitialize combobox script for the new card (if needed)
            initializeComboboxes(shiftCard);
        });

        // Remove shift card on click
        document.getElementById('shiftCardsContainer').addEventListener('click', function(event) {
            if (event.target.classList.contains('removeShiftCard')) {
                event.target.closest('.card').remove();
            }
        });
    </script>

    <script>
        document.getElementById('submitShiftForm').addEventListener('click', async function(event) {
            event.preventDefault(); // Prevent traditional form submission

            // Create FormData object
            const formData = new FormData();
            const shiftCards = document.querySelectorAll('#shiftCardsContainer .card-body');

            if (shiftCards.length === 0) {
                alert("Klik tombol tambah kolom untuk menambahkan jadwal shift.");
                return;
            }

            shiftCards.forEach((card, index) => {
                formData.append(`shifts[${index}][start_date]`, card.querySelector(
                    '[name*="[start_date]"]').value);
                formData.append(`shifts[${index}][end_date]`, card.querySelector('[name*="[end_date]"]')
                    .value);
                formData.append(`shifts[${index}][shift_id]`, card.querySelector('[name*="[shift_id]"]')
                    .value);
                formData.append(`shifts[${index}][is_dayoff]`, card.querySelector(
                    '[name*="[is_dayoff]"]:checked')?.value ?? '');

                // Get selected employees
                const employeesInput = card.querySelector('[name*="[shift_employees]"]').value;
                const employees = JSON.parse(employeesInput || '[]');
                employees.forEach((emp, empIndex) => {
                    formData.append(`shifts[${index}][shift_employees][${empIndex}]`, emp);
                });
            });

            try {
                // Axios request
                const response = await axios({
                    method: 'post',
                    url: "{{ route('api.v1.work-schedule.shift-rotating.store') }}",
                    data: formData,
                    headers: {
                        'Content-Type': 'multipart/form-data',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                });

                // Success Handling
                if (response.status === 200 || response.status === 201) {
                    window.location.href = '{{ route('work-schedule.shift-rotating.index') }}'; // Redirect
                }
            } catch (error) {
                if (error.response && error.response.status === 422) {
                        // Display validation errors
                        const errors = error.response.data.messages ||
                    []; // Assuming the validation errors are a flat array
                        const errorContainer = document.getElementById('error-container');
                        errorContainer.innerHTML = '';

                        errors.forEach((error) => {
                            const errorParagraph = document.createElement('p');
                            errorParagraph.textContent = error;
                            errorParagraph.classList.add('text-gray-700', 'text-2sm', 'font-normal');
                            errorContainer.appendChild(errorParagraph);
                        });

                        // Scroll to the top where the error messages are displayed
                        window.scrollTo(0, 0);

                        const errorApiAttention = document.getElementById('error-api-attention');
                        errorApiAttention.classList.remove('hidden');
                    } else {
                    alert("An error occurred. Please try again and contact administrator.");
                }
            }
        });

        // Function to display validation errors
        function displayValidationErrors(errors) {
            let errorContainer = document.getElementById('error-container');
            errorContainer.innerHTML = '';

            Object.values(errors).flat().forEach(error => {
                const errorParagraph = document.createElement('p');
                errorParagraph.textContent = error;
                errorParagraph.classList.add('text-red-500', 'text-sm');
                errorContainer.appendChild(errorParagraph);
            });

            // Scroll to the error messages
            window.scrollTo(0, 0);
        }
    </script>
@endpush
