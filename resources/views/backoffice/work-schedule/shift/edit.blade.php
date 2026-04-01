@extends('layouts.main')

@section('content')
    <!-- Container -->
    @include('backoffice.work-schedule.partials.submenu')
    <!-- Container -->
    <form action="{{ route('work-schedule.shift.update',$data['shift']['id']) }}" method="post">
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
                        <a class="btn text-center btn-sm btn-primary" href="{{ route('work-schedule.shift.index') }}">
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
                        <!-- Name Field -->
                        <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                            <label class="form-label max-w-56">
                                Nama Shift
                            </label>
                            <input class="input" name="name" placeholder="Nama Shift" type="text"
                                value="{{ old('name',$data['shift']['name']) }}" />
                        </div>
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                            <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                                <label class="form-label max-w-56">
                                    Waktu Masuk
                                </label>
                                <input class="input" name="start_time" placeholder="hh:mm" type="time"
                                    value="{{ old('start_time',$data['shift']['start_time']) }}" />
                            </div>
                            <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                                <label class="form-label max-w-56">
                                    Waktu Keluar
                                </label>
                                <input class="input" name="end_time" placeholder="hh:mm" type="time"
                                    value="{{ old('end_time',$data['shift']['end_time']) }}" />
                            </div>
                        </div>
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                            <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                                <label class="form-label max-w-56">
                                    Durasi Jam Kerja
                                </label>
                                <input class="input" name="duration_hours" placeholder="Durasi Jam Kerja" type="number" min="1"
                                    value="{{ old('duration_hours',$data['shift']['duration_hours']) }}" />
                            </div>

                            <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                                <label class="form-label max-w-56">
                                    Jam Masuk dan Jam Keluar Berbeda Hari?
                                </label>
                                <div class="flex gap-12">
                                    <label class="form-label flex items-center gap-2.5 text-nowrap">
                                        <input class="radio" name="is_night_shift" type="radio" value="1"
                                            {{ old('is_night_shift', $data['shift']['is_night_shift']) == '1' ? 'checked' : '' }} />
                                        Ya
                                    </label>
                                    <label class="form-label flex items-center gap-2.5 text-nowrap">
                                        <input class="radio" name="is_night_shift" type="radio" value="0"
                                            {{ old('is_night_shift',$data['shift']['is_night_shift']) == '0' ? 'checked' : '' }} />
                                        Tidak
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Status Field -->

                    </div>
                </div>
            </div>
        </div>
    </form>

    <!-- End of Container -->
@endsection
