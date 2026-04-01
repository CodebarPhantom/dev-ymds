@extends('layouts.main')

@section('content')
    @php
        use App\Enums\LeaveStatus;
    @endphp

    <!-- Container -->
    @include('backoffice.workforce.form.partials.submenu')

    <!-- Container -->
    <form action="{{route('workforce.submitted-form.leave.update', $data['leave']['id']) }}" method="post">
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
                            href="{{ route('workforce.submitted-form.leave.index') }}">
                            <i class="ki-filled ki-left"></i></i>Kembali
                        </a>
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
                        <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                            <label class="form-label max-w-56">
                                Karyawan
                            </label>
                            <input class="input" name="name" placeholder="Nama" type="input" readonly="true"
                                value="{{ $data['leave']['employee']['name'] }}" />
                        </div>
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">


                            <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                                <label class="form-label max-w-56">
                                    Dari Tanggal
                                </label>
                                <input class="input" name="start_date" placeholder="mm/dd/yyyy" type="date"
                                    readonly="true" value="{{ $data['leave']['start_date'] }}" />
                            </div>
                            <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                                <label class="form-label max-w-56">
                                    Sampai Tanggal
                                </label>
                                <input class="input" name="end_date" placeholder="mm/dd/yyyy" type="date"
                                    readonly="true" value="{{ $data['leave']['end_date'] }}" />
                            </div>
                        </div>
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

                            <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                                <label class="form-label max-w-56">
                                    Jumlah Hari yang diminta
                                </label>
                                <input class="input" name="start_date" placeholder="Type" type="input" readonly="true"
                                    value="{{ $data['leave']['request_day'] }}" />
                            </div>
                            <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                                <label class="form-label max-w-56">
                                    Jumlah Hari yang disetujui
                                </label>
                                <input class="input" name="start_date" placeholder="-" type="input" readonly="true"
                                    value="{{ $data['leave']['approve_day'] }}" />
                            </div>
                            <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                                <label class="form-label max-w-56">
                                    Tipe Cuti
                                </label>
                                <select class="select" name="type">
                                    <option {{ $data['leave']['type'] === 'pengurangan' ? 'selected' : '' }}
                                        value="pengurangan">
                                        Pengurangan
                                    </option>
                                    <option {{ $data['leave']['type'] === 'penambahan' ? 'selected' : '' }}
                                        value="penambahan">
                                        Penambahan
                                    </option>
                                    <option {{ $data['leave']['type'] === 'khusus' ? 'selected' : '' }} value="khusus">
                                        Khusus
                                    </option>
                                    <option {{ $data['leave']['type'] === 'reset' ? 'selected' : '' }} value="reset">
                                        Reset
                                    </option>
                                </select>
                            </div>
                            <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                                <label class="form-label max-w-56">
                                    Status
                                </label>
                                <span class="badge badge-pill badge-outline badge-{{ $data['leave']['status_color'] }}">
                                    {{ $data['leave']['status_label'] }}
                                </span>
                            </div>
                        </div>
                        <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                            <label class="form-label max-w-56">
                                Catatan
                            </label>
                            <textarea class="textarea" name="note" placeholder="Catatan cuti" rows="3" type="text" readonly="true" />{{ $data['leave']['note'] }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="container-fixed mt-5">
            <div class="grid gap-5  mx-auto">
                <div class="card pb-2.5">
                    <div class="card-header">
                        <h3 class="card-title">
                            Log Cuti
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="flex flex-col">
                            @if ($data['leave']['known_at'])
                                <div class="flex items-start relative">
                                    <div
                                        class="w-9 left-0 top-9 absolute bottom-0 translate-x-1/2 border-l border-l-gray-300">
                                    </div>
                                    <div
                                        class="flex items-center justify-center shrink-0 rounded-full bg-gray-100 border border-gray-300 size-9 text-gray-600">
                                        <i class="ki-filled ki-file-right text-primary">
                                        </i>
                                    </div>
                                    <div class="pl-2.5 mb-7 text-md grow">
                                        <div class="flex flex-col">
                                            <div class="text-sm text-gray-800">
                                                Diketahui oleh
                                                <a class="text-sm font-medium link" href="#">
                                                    {{ $data['leave']->knownBy->name }}
                                                </a>
                                            </div>
                                            <span class="text-xs text-gray-600">
                                                {{ $data['leave']['formatted_known_at'] }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @if ($data['leave']['approved_at'])
                                <div class="flex items-start relative">
                                    <div
                                        class="w-9 left-0 top-9 absolute bottom-0 translate-x-1/2 border-l border-l-gray-300">
                                    </div>
                                    <div
                                        class="flex items-center justify-center shrink-0 rounded-full bg-gray-100 border border-gray-300 size-9 text-gray-600">
                                        <i class="ki-filled ki-information-2 text-info">
                                        </i>
                                    </div>
                                    <div class="pl-2.5 mb-7 text-md grow">
                                        <div class="flex flex-col">
                                            <div class="text-sm text-gray-800">
                                                Disetujui Oleh
                                                <a class="text-sm font-medium link" href="#">
                                                    {{ $data['leave']->approvedBy->name }}
                                                </a>
                                            </div>
                                            <span class="text-xs text-gray-600">
                                                {{ $data['leave']['formatted_approved_at'] }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-start relative">
                                    <div
                                        class="w-9 left-0 top-9 absolute bottom-0 translate-x-1/2 border-l border-l-gray-300">
                                    </div>
                                    <div
                                        class="flex items-center justify-center shrink-0 rounded-full bg-gray-100 border border-gray-300 size-9 text-gray-600">
                                        <i class="ki-filled ki-check-circle text-success">
                                        </i>
                                    </div>
                                    <div class="pl-2.5 mb-7 text-md grow">
                                        <div class="flex flex-col">
                                            <div class="text-sm text-gray-800">
                                                Cuti Disetujui
                                            </div>
                                            <span class="text-xs text-gray-600">
                                                {{ $data['leave']['formatted_approved_at'] }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            @if ($data['leave']['status']->value === LeaveStatus::DITOLAK->value)
                                <div class="flex items-start relative">
                                    <div
                                        class="flex items-center justify-center shrink-0 rounded-full bg-gray-100 border border-gray-300 size-9 text-gray-600">
                                        <i class="ki-filled ki-file-right text-danger">
                                        </i>
                                    </div>
                                    <div class="pl-2.5 text-md grow">
                                        <div class="flex flex-col">
                                            <div class="text-sm text-gray-800">
                                                Ditolak Oleh
                                                <a class="text-sm link" href="#">
                                                    {{ $data['leave']->rejectBy->name }}
                                                </a>
                                            </div>
                                            <span class="text-xs text-gray-600">
                                                {{ $data['leave']['formatted_updated_at'] }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @if ($data['leave']['status']->value === LeaveStatus::DIBATALKAN->value)
                                <div class="flex items-start relative">
                                    <div
                                        class="flex items-center justify-center shrink-0 rounded-full bg-gray-100 border border-gray-300 size-9 text-gray-600">
                                        <i class="ki-filled ki-file-right text-warning">
                                        </i>
                                    </div>
                                    <div class="pl-2.5 text-md grow">
                                        <div class="flex flex-col">
                                            <div class="text-sm text-gray-800">
                                                Cuti Dibatalkan
                                            </div>
                                            <span class="text-xs text-gray-600">
                                                {{ $data['leave']['formatted_updated_at'] }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
    <!-- End of Container -->
@endsection


@push('javascript')
    <script type="text/javascript">
        function confirmLeaveApproval(event) {
            if (!confirm("Are you sure you want to approve this leave?")) {
                event.preventDefault(); // Prevent form submission if user cancels
                return false;
            }
            return true; // Allow form submission if user confirms
        }

        function rejectLeave(event) {
            if (!confirm("Are you sure you want to reject this leave?")) {
                event.preventDefault(); // Prevent form submission if user cancels
                return false;
            }
            return true; // Allow form submission if user confirms
        }
    </script>
@endpush
