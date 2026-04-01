@extends('layouts.main')

@section('content')
    <!-- Container -->
    @include('backoffice.work-schedule.partials.submenu')
    <!-- Container -->
    <form action="{{ route('work-schedule.shift-fixed.update',$data['shiftFixed']['id']) }}" method="post">
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
                        <a class="btn text-center btn-sm btn-primary" href="{{ route('work-schedule.shift-fixed.index') }}">
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
                        <div class="card-body grid gap-5">
                            <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                                <label class="form-label max-w-56">Jabatan</label>
                                <div class="relative inline-block w-full combobox"
                                    data-api="{{ route('api.v1.roles.get-all-role') }}"
                                    data-collection="roles"
                                    data-multiple="true">
                                    <div
                                        class="pill-container flex flex-wrap items-center w-full px-2 py-2 border border-gray-300 rounded-md bg-white focus:ring-2 focus:ring-blue-500">
                                        <input type="text"
                                            class="search-box flex-grow px-2 py-1 text-sm text-gray-700 bg-transparent border-none outline-none" />
                                    </div>
                                    <input type="hidden" class="selected-data" name="roles" value="{{ $data['roleShiftIdsString'] }}" />
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
                                    <option value="" selected="">--- Silahkan Pilih ---</option>
                                    @foreach ($data['shifts'] as $shift)
                                        <option {{ $data['shiftFixed']['shift_id'] === $shift->id  ? 'selected' : '' }} value={{ $shift->id}} > {{ $shift->name }}  {{ $shift->formatted_start_time }} - {{ $shift->formatted_end_time }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                                <label class="form-label max-w-56">
                                    Nama Shift
                                </label>
                                <input class="input" name="name" placeholder="Nama Shift"  value="{{$data['shiftFixed']['name'] }}" type="text"
                                    value="{{ old('name') }}" />
                            </div>

                            <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                                <label class="form-label max-w-56">
                                    Hari Libur
                                </label>
                                <div class="flex flex-col gap-2.5">
                                    <label class="switch">
                                        <input
                                            type="checkbox"
                                            name="day_off[]"
                                            value="1"
                                            {{ in_array(1, $data['shiftFixed']['day_off']) ? 'checked' : '' }} />
                                        <span class="switch-label">Senin</span>
                                    </label>
                                    <label class="switch">
                                        <input
                                            type="checkbox"
                                            name="day_off[]"
                                            value="2"
                                            {{ in_array(2, $data['shiftFixed']['day_off']) ? 'checked' : '' }}/>
                                        <span class="switch-label">Selasa</span>
                                    </label>
                                    <label class="switch">
                                        <input
                                            type="checkbox"
                                            name="day_off[]"
                                            value="3"
                                            {{ in_array(3, $data['shiftFixed']['day_off']) ? 'checked' : '' }} />
                                        <span class="switch-label">Rabu</span>
                                    </label>
                                    <label class="switch">
                                        <input
                                            type="checkbox"
                                            name="day_off[]"
                                            value="4"
                                            {{ in_array(4, $data['shiftFixed']['day_off']) ? 'checked' : '' }} />
                                        <span class="switch-label">Kamis</span>
                                    </label>
                                    <label class="switch">
                                        <input
                                            type="checkbox"
                                            name="day_off[]"
                                            value="5"
                                            {{ in_array(5, $data['shiftFixed']['day_off']) ? 'checked' : '' }} />
                                        <span class="switch-label">Jumat</span>
                                    </label>
                                    <label class="switch">
                                        <input
                                            type="checkbox"
                                            name="day_off[]"
                                            value="6"
                                            {{ in_array(6, $data['shiftFixed']['day_off']) ? 'checked' : '' }} />
                                        <span class="switch-label">Sabtu</span>
                                    </label>
                                    <label class="switch">
                                        <input
                                            type="checkbox"
                                            name="day_off[]"
                                            value="0"
                                            {{ in_array(0, $data['shiftFixed']['day_off']) ? 'checked' : '' }} />
                                        <span class="switch-label">Minggu</span>
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
@include('partials.advanced-selectbox')
