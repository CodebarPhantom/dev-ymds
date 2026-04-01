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
                        href="{{ route('workforce.employee-payroll-comben.index') }}">
                        <i class="ki-filled ki-left"></i></i>Kembali
                    </a>
                </div>
                @if (!$data['comben']['is_paid'])
                    <div class="flex items-center gap-2.5">
                        <a class="btn btn-sm text-center btn-warning"
                            href="{{ route('workforce.employee-payroll-comben.edit', $data['comben']['id']) }}">
                            <i class="ki-filled ki-notepad-edit"></i>{{ $data['editPageTitle'] }}
                        </a>
                    </div>
                @endif

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
                            <input class="input" name="name" placeholder="Nama" type="input" readonly="true"
                                value="{{ old('name', $data['comben']['employee']['name']) }}" />
                        </div>
                        <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                            <label class="form-label max-w-56">
                                Tanggal
                            </label>
                            <input class="input" name="date" placeholder="Tanggal" type="date" readonly="true"
                                value="{{ old('date', $data['comben']['date']) }}" />
                        </div>

                    </div>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                            <label class="form-label max-w-56">
                                Catatan Compensation Benefit
                            </label>
                            <input class="input" name="note" placeholder="Catatan Compensation" type="input"
                                readonly="true" value="{{ old('note', $data['comben']['note']) }}" />
                        </div>
                        <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                            <label class="form-label max-w-56">
                                Nominal
                            </label>
                            <input readonly="true" value="{{ old('value', number_format($data['comben']['value'], 2, '.', ',')) }}"
                                    type="text" placeholder="Nilai" name="value"
                                    class="input w-full border border-gray-300 p-2 text-right format-number">
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- End of Container -->
@endsection
@include('partials.advanced-selectbox')
