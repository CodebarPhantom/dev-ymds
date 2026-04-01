@extends('layouts.main')

@section('content')

    <!-- Container -->
    @include('backoffice.work-schedule.partials.submenu')
    <!-- Container -->
    <form action="{{ route('work-schedule.shift-rotating.update',$data['shiftRotating']['id']) }}" method="post">
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
                            href="{{ route('work-schedule.shift-rotating.index') }}">
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
                                    Kategori Jadwal
                                </label>
                                <div class="flex gap-12">
                                    <label class="form-label flex items-center gap-2.5 text-nowrap">
                                        <input {{ $data['shiftRotating']['is_dayoff'] == false ? 'checked' : '' }} class="radio " name="is_dayoff" type="radio" value="0" />
                                        Jadwal Masuk
                                    </label>
                                    <label class="form-label flex items-center gap-2.5 text-nowrap ">
                                        <input {{ $data['shiftRotating']['is_dayoff'] == true ? 'checked' : '' }} class="radio" name="is_dayoff" type="radio" value="1" />
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
                                    value="{{ old('start_date',$data['shiftRotating']['start_date']) }}" />
                            </div>
                            <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                                <label class="form-label max-w-56">
                                    Sampai Tanggal
                                </label>
                                <input class="input" name="end_date" placeholder="mm/dd/yyyy" type="date"
                                    value="{{ old('end_date',$data['shiftRotating']['start_date']) }}" />
                            </div>
                        </div>


                        <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                            <label class="form-label max-w-56">Karyawan Shift</label>
                            <div class="relative inline-block w-full combobox"
                                data-api="{{ route('api.v1.workforce.employee.get-all') }}"
                                data-collection="employees"
                                data-multiple="true">
                                <div
                                    class="pill-container flex flex-wrap items-center w-full px-2 py-2 border border-gray-300 rounded-md bg-white focus:ring-2 focus:ring-blue-500">
                                    <input type="text"
                                        class="search-box flex-grow px-2 py-1 text-sm text-gray-700 bg-transparent border-none outline-none"  />
                                </div>
                                <input type="hidden" class="selected-data" name="shift_employees" value="{{ $data['employeeShiftIdsString'] }}" />
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
                            <select class="select" name="shift_id" required >
                                <option value="" selected>--- Silahkan Pilih ---</option>
                                @foreach ($data['shifts'] as $shift)
                                    <option value="{{ $shift->id }}" {{ old('shift_id', $data['shiftRotating']['shift_id']) == $shift->id ? 'selected' : '' }}>
                                        {{ $shift->name }} {{ $shift->formatted_start_time }} -
                                        {{ $shift->formatted_end_time }}
                                    </option>
                                @endforeach
                            </select>
                        </div>


                    </div>
                </div>
            </div>
        </div>
    </form>

    <!-- End of Container -->
@endsection
@include('partials.advanced-selectbox')
