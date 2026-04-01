@extends('layouts.main')

@section('content')
    <!-- Container -->
    <form action="{{ route('workforce.employee-payroll-deduction.update', $data['deduction']['id']) }}" method="post">
        @csrf
        @method('PUT')
        <div class="container-fixed">
            <div class="flex flex-wrap items-center lg:items-end justify-between gap-5 pb-7.5">
                <div class="flex flex-col justify-center gap-2">
                    <h1 class="text-xl font-bold leading-none text-gray-900">
                        {{ $data['pageTitle'] }}
                    </h1>
                </div>
                <div class="flex items-center gap-2.5">
                    <div class="flex items-center gap-2.5">
                        <a class="btn text-center btn-sm btn-primary"
                            href="{{ route('workforce.employee-payroll-deduction.index') }}">
                            <i class="ki-filled ki-left"></i></i>Kembali
                        </a>
                    </div>
                    <div class="flex items-center gap-2.5">
                        <button type="submit" class="btn btn-sm text-center btn-success">
                            <i class="ki-filled ki-check"></i>{{ $data['pageTitle'] }}
                        </button>
                    </div>
                </div>

            </div>
        </div>

        @include('partials.attention')

        <div class="container-fixed">
            <div class="grid gap-5  mx-auto">
                <div class="card pb-2.5">
                    <div class="card-body grid gap-5">

                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                            <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                                <label class="form-label max-w-56">
                                    Nama
                                </label>
                                <div class="relative inline-block w-full combobox"
                                    data-options='@json($data['employees'])' data-multiple="false">
                                    <div
                                        class="pill-container flex items-center w-full px-2 py-2 border border-gray-300 rounded-md bg-white focus:ring-2 focus:ring-blue-500">
                                        <input type="text" placeholder="Search..."
                                            class="search-box flex-grow px-2 py-1 text-sm text-gray-700 bg-transparent border-none outline-none" />
                                    </div>
                                    <input id="employee_id" type="hidden" class="selected-data" name="employee_id"
                                        value="{{ $data['deduction']['employee_id'] }}" />
                                    <div
                                        class="dropdown-menu absolute z-10 w-full mt-2 bg-white border border-gray-300 rounded-md shadow-lg hidden">
                                        <div class="options-container max-h-40 overflow-y-auto"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                                <label class="form-label max-w-56">
                                    Tanggal
                                </label>
                                <input class="input" name="date" placeholder="Tanggal" type="date"
                                    value="{{ old('date', $data['deduction']['date']) }}" />
                            </div>

                        </div>
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                            <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                                <label class="form-label max-w-56">
                                    Catatan Deduction
                                </label>
                                <input class="input" name="note" placeholder="Catatan Deduction" type="input"
                                    value="{{ old('note', $data['deduction']['note']) }}" />
                            </div>
                            <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                                <label class="form-label max-w-56">
                                    Nominal
                                </label>
                                <input value="{{ old('value', number_format($data['deduction']['value'], 2, '.', ',')) }}"
                                    type="text" placeholder="Nilai" name="value"
                                    class="input w-full border border-gray-300 p-2 text-right format-number">
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </form>

    <!-- End of Container -->
@endsection
@include('partials.advanced-selectbox')

@push('javascript')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            formatNumber();
            deleteRemunerationPackage();
        });

        function formatNumber() {
            const inputs = document.querySelectorAll('.format-number');

            inputs.forEach(input => {
                // Format input value on focusout
                input.addEventListener('blur', (e) => {
                    let value = parseFloat(input.value.replace(/,/g, '')) ||
                        0; // Remove separators and parse as float
                    input.value = value.toLocaleString('en-US', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    }); // Format with separator
                });

                // Convert formatted value back to a plain decimal number before submitting
                input.addEventListener('input', (e) => {
                    let rawValue = e.target.value.replace(/,/g, ''); // Remove separators on input
                    e.target.value = rawValue; // Set the raw value in the input field
                });
            });

            // Ensure the correct value is sent in the form
            document.querySelector('form').addEventListener('submit', function() {
                inputs.forEach(input => {
                    input.value = parseFloat(input.value.replace(/,/g, '')) ||
                        0; // Remove separators and keep only the decimal value
                });
            });
        };
    </script>
@endpush
