@extends('layouts.main')

@section('content')
    <!-- Container -->
    @include('backoffice.work-schedule.partials.submenu')
    <!-- Container -->
    <form action="{{ route('work-schedule.holiday.store') }}" method="post">
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
                        <a class="btn text-center btn-sm btn-primary" href="{{ route('work-schedule.holiday.index') }}">
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
                                Nama Hari Libur
                            </label>
                            <input class="input" name="name" placeholder="Nama Hari Libur" type="text"
                                value="{{ old('name') }}" />
                        </div>

                        <!-- Date Field -->
                        <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                            <label class="form-label max-w-56">
                                Tanggal
                            </label>
                            <input class="input" name="date" placeholder="mm/dd/yyyy" type="date"
                                value="{{ old('date') }}" />
                        </div>

                        <!-- Status Field -->
                        {{-- <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                            <label class="form-label max-w-56">
                                Berlaku untuk shift?
                            </label>
                            <div class="flex gap-12">
                                <label class="form-label flex items-center gap-2.5 text-nowrap">
                                    <input class="radio" name="applies_to_shift" type="radio" value="1"
                                        {{ old('applies_to_shift', '1') == '1' ? 'checked' : '' }} />
                                    Ya
                                </label>
                                <label class="form-label flex items-center gap-2.5 text-nowrap">
                                    <input class="radio" name="applies_to_shift" type="radio" value="0"
                                        {{ old('applies_to_shift') == '0' ? 'checked' : '' }} />
                                    Tidak
                                </label>
                            </div>
                        </div> --}}
                        <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                            <label class="form-label max-w-56">Jabatan yang Libur</label>
                            <div class="relative inline-block w-full combobox"
                                 data-api="{{ route('api.v1.roles.get-all-role') }}"
                                 data-collection="roles"
                                 data-multiple="true">
                                <div
                                    class="pill-container flex items-center w-full px-2 py-2 border border-gray-300 rounded-md bg-white focus:ring-2 focus:ring-blue-500">
                                    <input type="text" placeholder="Search..."
                                           class="search-box flex-grow px-2 py-1 text-sm text-gray-700 bg-transparent border-none outline-none" />
                                </div>
                                <input type="hidden" class="selected-data" name="roles" value="" />
                                <div
                                    class="dropdown-menu absolute z-10 w-full mt-2 bg-white border border-gray-300 rounded-md shadow-lg hidden">
                                    <div class="options-container max-h-40 overflow-y-auto"></div>
                                </div>
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
