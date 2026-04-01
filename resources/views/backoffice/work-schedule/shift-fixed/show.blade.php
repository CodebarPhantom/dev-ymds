@extends('layouts.main')

@section('content')
    <!-- Container -->
    @include('backoffice.work-schedule.partials.submenu')
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
                    <a class="btn text-center btn-sm btn-primary" href="{{ route('work-schedule.shift-fixed.index') }}">
                        <i class="ki-filled ki-left"></i></i>Kembali
                    </a>
                </div>
                <div class="flex items-center gap-2.5">
                    <a class="btn btn-sm text-center btn-warning"
                        href="{{ route('work-schedule.shift-fixed.edit', $data['shiftFixed']['id']) }}">
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
                    <!-- Name Field -->
                    <div class="card-body grid gap-5">
                        <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                            <label class="form-label max-w-56">Jabatan</label>
                            <div class="relative inline-block w-full combobox"
                                data-api="{{ route('api.v1.roles.get-all-role') }}" data-collection="roles"
                                data-multiple="true">
                                <div
                                    class="pill-container flex flex-wrap items-center w-full px-2 py-2 border border-gray-300 rounded-md bg-white focus:ring-2 focus:ring-blue-500">
                                    <input type="text"
                                        class="search-box flex-grow px-2 py-1 text-sm text-gray-700 bg-transparent border-none outline-none"
                                        disabled />
                                </div>
                                <input type="hidden" class="selected-data" name="roles"
                                    value="{{ $data['roleShiftIdsString'] }}" />
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
                            <select class="select" name="shift_id" required disabled>
                                <option value="" selected="">--- Silahkan Pilih ---</option>
                                @foreach ($data['shifts'] as $shift)
                                    <option {{ $data['shiftFixed']['shift_id'] === $shift->id ? 'selected' : '' }}
                                        value={{ $shift->id }}> {{ $shift->name }} {{ $shift->formatted_start_time }} -
                                        {{ $shift->formatted_end_time }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                            <label class="form-label max-w-56">
                                Nama Shift
                            </label>
                            <input class="input" name="name" placeholder="Nama Shift" readonly="true"
                                value="{{ $data['shiftFixed']['name'] }}" type="text" value="{{ old('name') }}" />
                        </div>

                        <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                            <label class="form-label max-w-56">
                                Hari Libur
                            </label>
                            <div class="flex flex-col gap-2.5">
                                <label class="switch">
                                    <input type="checkbox" name="day_off[]" value="1" disabled
                                        {{ in_array(1, $data['shiftFixed']['day_off']) ? 'checked' : '' }} />
                                    <span class="switch-label">Senin</span>
                                </label>
                                <label class="switch">
                                    <input type="checkbox" name="day_off[]" value="2" disabled
                                        {{ in_array(2, $data['shiftFixed']['day_off']) ? 'checked' : '' }} />
                                    <span class="switch-label">Selasa</span>
                                </label>
                                <label class="switch">
                                    <input type="checkbox" name="day_off[]" value="3" disabled
                                        {{ in_array(3, $data['shiftFixed']['day_off']) ? 'checked' : '' }} />
                                    <span class="switch-label">Rabu</span>
                                </label>
                                <label class="switch">
                                    <input type="checkbox" name="day_off[]" value="4" disabled
                                        {{ in_array(4, $data['shiftFixed']['day_off']) ? 'checked' : '' }} />
                                    <span class="switch-label">Kamis</span>
                                </label>
                                <label class="switch">
                                    <input type="checkbox" name="day_off[]" value="5" disabled
                                        {{ in_array(5, $data['shiftFixed']['day_off']) ? 'checked' : '' }} />
                                    <span class="switch-label">Jumat</span>
                                </label>
                                <label class="switch">
                                    <input type="checkbox" name="day_off[]" value="6" disabled
                                        {{ in_array(6, $data['shiftFixed']['day_off']) ? 'checked' : '' }} />
                                    <span class="switch-label">Sabtu</span>
                                </label>
                                <label class="switch">
                                    <input type="checkbox" name="day_off[]" value="0" disabled
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
    <br>
    <div class="container-fixed">
        <div class="grid pb-7.5">
            <div class="card card-grid min-w-full">
                <div class="card-body">
                    <div id="kt_remote_table">
                        <div class="scrollable-x-auto">
                            <table class="table table-auto table-border align-middle text-gray-700 font-medium text-sm"
                                data-datatable-table="true">
                                <thead>
                                    <tr>
                                        <th class="text-center" data-datatable-column="period">
                                            <span class="sort">
                                                <span class="sort-label">
                                                    Periode
                                                </span>
                                            </span>
                                        </th>
                                        <th class="text-center" data-datatable-column="action">
                                            <span class="sort">
                                                <span class="sort-label">
                                                    Action
                                                </span>
                                            </span>
                                        </th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                        <div
                            class="card-footer justify-center md:justify-between flex-col md:flex-row gap-3 text-gray-600 text-2sm font-medium">
                            <div class="flex items-center gap-2">
                                Show
                                <select class="select select-sm w-16" data-datatable-size="true" name="perpage">
                                </select>
                                per page
                            </div>
                            <div class="flex items-center gap-4">
                                <span data-datatable-info="true">
                                </span>
                                <div class="pagination" data-datatable-pagination="true">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End of Container -->
    @include('partials.modal-confirm-delete', [
        'mainTitle' => 'Hapus shift log?',
        'mainContent' =>
            'Apakah anda yakin untuk menghapus shift log semua jadwal yang sudah dibuat pada periode ini akan terhapus?',
    ])

    <!-- End of Container -->
@endsection
@include('partials.advanced-selectbox')
<!-- Begin - Define Route -->
<!-- End - Define Route -->
@push('javascript')
    <script type="text/javascript">
        const apiUrl =
            '{{ route('api.v1.work-schedule.shift-fixed.shift-fixed-log-datatable', $data['shiftFixed']['id']) }}';
        const deleteUrl =
            "{{ route('api.v1.work-schedule.shift-fixed.shift-fixed-log-destroy', ':id') }}";
        const element = document.querySelector('#kt_remote_table');

        const dataTableOptions = {
            apiEndpoint: apiUrl,
            pageSize: 10,
            searchQuery: '',
            infoEmpty: 'Data Kosong',
            stateSave: false,
            columns: {
                period: {
                    title: 'Periode',
                    render: (item, row, context) => {
                        return row.formatted_period
                    },
                    createdCell(cell) {
                        cell.classList.add('text-center');
                    },
                },
                action: {
                    title: 'Action',
                    render: (item, row, context) => {
                        return `
                            <button class="btn btn-icon btn-sm btn-clear btn-danger" id="delete" data-delete-id="${row.id}"  data-modal-toggle="#modal_confirm_delete" onclick="openDeleteModal(${row.id},'modal_confirm_delete')">
                                <i class="ki-filled ki-trash"></i>
                            </button>
                            <div class="tooltip transition-opacity duration-300" id="delete">
                                Hapus {{ $data['pageTitle'] }}
                            </div>
                        `;
                    },
                    createdCell(cell) {
                        cell.classList.add('text-center');
                    }
                },
            }
        };
        const dataTable = new KTDataTable(element, dataTableOptions);
        dataTable.search('');


    </script>
@endpush
