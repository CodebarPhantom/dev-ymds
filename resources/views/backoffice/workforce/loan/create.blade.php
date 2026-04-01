@extends('layouts.main')

@section('content')
    <!-- Container -->

    <!-- Container -->
    <form action="{{ route('workforce.employee-loan.store') }}" method="post">
        @csrf
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
                            href="{{ route('workforce.employee-loan.index') }}">
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
                                    Karyawan
                                </label>
                                <div class="relative inline-block w-full combobox"
                                    data-options='@json($data['employees'])' data-multiple="false">
                                    <div
                                        class="pill-container flex items-center w-full px-2 py-2 border border-gray-300 rounded-md bg-white focus:ring-2 focus:ring-blue-500">
                                        <input type="text" placeholder="Search..."
                                            class="search-box flex-grow px-2 py-1 text-sm text-gray-700 bg-transparent border-none outline-none" />
                                    </div>
                                    <input id="employee_id" type="hidden" class="selected-data" name="employee_id" required />
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
                                <input class="input" name="loan_date" placeholder="mm/dd/yyyy" type="date" required value="" />
                            </div>
                        </div>
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                            <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                                <label class="form-label max-w-56">
                                    Nilai Pinjaman
                                </label>
                                <input value="{{ old('loan_amount') }}"
                                    type="text" placeholder="10,000,000" name="loan_amount" required
                                    class="input w-full border border-gray-300 p-2 text-right format-number">
                            </div>
                            <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                                <label class="form-label max-w-56">
                                    Periode Angsuran
                                </label>
                                <input value="{{ old('installment_period') }}"
                                    type="number" min="1" placeholder="3" name="installment_period" required
                                    class="input w-full border border-gray-300 p-2 text-right">
                            </div>
                        </div>
                        <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                            <label class="form-label max-w-56">
                                Catatan
                            </label>
                            <textarea class="textarea" name="notes" placeholder="Catatan pinjaman" rows="3" type="text" value="" /></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <!-- End of Container -->
@endsection
@include('partials.advanced-selectbox')
@include('partials.format-number')

@push('javascript')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            formatNumber();
        });
    </script>
@endpush
