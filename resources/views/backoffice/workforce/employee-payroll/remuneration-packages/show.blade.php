@extends('layouts.main')

@section('content')
    <!-- Container -->

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
                            href="{{ route('workforce.employee-role-remuneration-package.index') }}">
                            <i class="ki-filled ki-left"></i></i>Kembali
                        </a>
                    </div>
                    <div class="flex items-center gap-2.5">
                        <a class="btn btn-sm text-center btn-warning"
                            href="{{ route('workforce.employee-role-remuneration-package.edit', $data['role']['id']) }}">
                            <i class="ki-filled ki-notepad-edit"></i>{{ $data['editPageTitle'] }}
                        </a>
                    </div>
                </div>

            </div>
        </div>

        @include('partials.attention')

        <div class="container-fixed">
            <div class="grid gap-5  mx-auto">
                <div class="card pb-2.5">
                    <div class="card-body grid gap-5">
                        <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                            <label class="form-label max-w-56">
                                Jabatan
                            </label>
                            <input class="input" readonly="true" value="{{ $data['role']['name'] }}"
                                placeholder="Nama Jabatan" id="role-name" type="input" />
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid gap-5 mt-5 mx-auto">
                <div class="card pb-2.5">
                    <div class="card-header flex flex-wrap items-center lg:items-end justify-between gap-5 ">
                        <h3 class="card-title ">
                            Paket Remunerasi
                        </h3>
                    </div>
                    <div class="card-body grid gap-5" id="remuneration-package-container">
                        <div class="flex gap-2 items-center">
                            <label class="form-label text-center">
                                Nama Remunerasi
                            </label>
                            <label class="form-label text-center">
                                Nilai
                            </label>
                        </div>
                        @foreach ($data['role']['remunerationPackages'] as $remunerationPackage)
                            <div class="flex gap-3 items-center remuneration-package-row"
                                data-id="{{ $remunerationPackage->id }}">
                                <input type="hidden" name="id[]" value="{{ $remunerationPackage->id }}">
                                <input readonly="true" value="{{ $remunerationPackage->note }}" type="text" name="note[]"
                                    placeholder="Keterangan Remunerasi" class="input w-full border border-gray-300 p-2">
                                <input readonly="true" value="{{ number_format($remunerationPackage->value, 2, '.', ',') }}" type="text"
                                    placeholder="Nilai" name="value[]"
                                    class="input w-full border border-gray-300 p-2 text-right format-number">
                                <!-- Other fields here -->
                            </div>
                        @endforeach

                    </div>
                </div>
            </div>
        </div>

    <!-- End of Container -->
@endsection

@push('javascript')
    <script type="text/javascript">
        document.addEventListener('DOMContentLoaded', function() {
            formatNumber();
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
