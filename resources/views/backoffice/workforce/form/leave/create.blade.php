@extends('layouts.main')

@section('content')
    <!-- Container -->
    @include('backoffice.workforce.form.partials.submenu')
    <!-- Container -->
    <form action="{{ route('workforce.submitted-form.leave.store') }}" method="post">
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
                            href="{{ route('workforce.submitted-form.leave.index') }}">
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
                                <select class="select" name="employee_id" required>
                                    <option value="" selected="">--- Silahkan Pilih ---</option>
                                    @foreach ($data['employees'] as $employee)
                                        <option value={{ $employee->id}} > {{ $employee->name }}  </option>
                                    @endforeach

                                </select>
                            </div>

                            <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                                <label class="form-label max-w-56">
                                    Tipe Cuti
                                </label>
                                <select class="select" name="type">
                                    <option value="pengurangan">
                                        Pengurangan
                                    </option>
                                    <option value="penambahan">
                                        Penambahan
                                    </option>
                                    <option value="khusus">
                                        Khusus
                                    </option>
                                    <option value="reset">
                                        Reset
                                    </option>
                                </select>
                            </div>

                        </div>
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                            <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                                <label class="form-label max-w-56">
                                    Dari Tanggal
                                </label>
                                <input class="input" name="start_date" placeholder="mm/dd/yyyy" type="date"
                                    value="" />
                            </div>
                            <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                                <label class="form-label max-w-56">
                                    Sampai Tanggal
                                </label>
                                <input class="input" name="end_date" placeholder="mm/dd/yyyy" type="date"
                                    value="" />
                            </div>
                        </div>
                        <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                            <label class="form-label max-w-56">
                                Catatan
                            </label>
                            <textarea class="textarea" name="note" placeholder="Catatan cuti" rows="3" type="text" value="" /></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <!-- End of Container -->
@endsection
