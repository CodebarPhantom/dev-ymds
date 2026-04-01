@extends('layouts.main')

@section('content')
    @php
        use App\Enums\PermitStatus;
        use App\Enums\PermitType;

    @endphp

    <!-- Container -->
    @include('backoffice.workforce.form.partials.submenu')

    <!-- Container -->
    @csrf
    <div class="container-fixed">
        <div class="flex flex-wrap items-center lg:items-end justify-between gap-5 pb-7.5">
            <div class="flex flex-col justify-center gap-2">
                <h1 class="text-xl font-bold leading-none text-gray-900">
                    {{ $data['pageTitle'] }}
                </h1>
            </div>

            <div class="flex items-center gap-2.5">
                <a class="btn text-center btn-sm btn-primary" href="{{ route('workforce.submitted-form.permit.index') }}">
                    <i class="ki-filled ki-left"></i></i>Kembali
                </a>
                @if ($data['permit']['status']->value === PermitStatus::MENUNGGU_PERSETUJUAN_HR->value)
                    @if ($data['permit']['permit_type']->value === PermitType::SAKIT->value)
                        <form
                            action="{{ route('workforce.submitted-form.permit.confirm-permit-update', $data['permit']['id']) }}"
                            method="POST" onsubmit="return confirmPermitApproval(event)" class="inline">
                            @csrf
                            <input type="hidden" name="_method" value="PUT">
                            <button type="submit" class="btn text-center btn-sm btn-success" data-tooltip="#confirmPermit">
                                <i class="ki-filled ki-check"></i> Setujui Izin Tanpa Potong Gaji
                            </button>
                        </form>
                    @else

                        <form
                            action="{{ route('workforce.submitted-form.permit.confirm-permit-update-unpaid-leave', $data['permit']['id']) }}"
                            method="POST" onsubmit="return confirmPermitApproval(event)" class="inline">
                            @csrf
                            <input type="hidden" name="_method" value="PUT">
                            <button type="submit" class="btn text-center btn-sm btn-info" data-tooltip="#confirmPermit">
                                <i class="ki-filled ki-check"></i> Setujui Izin Dengan Potong Gaji
                            </button>
                        </form>

                        <form
                            action="{{ route('workforce.submitted-form.permit.confirm-permit-update', $data['permit']['id']) }}"
                            method="POST" onsubmit="return confirmPermitApproval(event)" class="inline">
                            @csrf
                            <input type="hidden" name="_method" value="PUT">
                            <button type="submit" class="btn text-center btn-sm btn-success" data-tooltip="#confirmPermit">
                                <i class="ki-filled ki-check"></i> Setujui Izin Tanpa Potong Gaji
                            </button>
                        </form>
                    @endif





                    <form
                        action="{{ route('workforce.submitted-form.permit.reject-permit-update', $data['permit']['id']) }}"
                        method="POST" onsubmit="return rejectPermit(event)" class="inline">
                        @csrf
                        <input type="hidden" name="_method" value="PUT">
                        <button type="submit" class="btn text-center btn-sm btn-danger" data-tooltip="#rejectPermit">
                            <i class="ki-filled ki-cross"></i> Tolak Izin
                        </button>
                    </form>
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
                                Tipe Izin
                            </label>
                            <span class="badge badge-pill badge-outline badge-{{ $data['permit']['permit_type_color'] }}">
                                {{ $data['permit']['permit_type_label'] }}
                            </span>
                        </div>
                        <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                            <label class="form-label max-w-56">
                                Status
                            </label>
                            <span class="badge badge-pill badge-outline badge-{{ $data['permit']['status_color'] }}">
                                {{ $data['permit']['status_label'] }}
                            </span>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                            <label class="form-label max-w-56">
                                Dari
                            </label>
                            <input class="input" name="start_date" placeholder="mm/dd/yyyy" type="text" readonly="true"
                                value="{{ $data['permit']['formatted_start_date'] }}" />
                        </div>
                        <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                            <label class="form-label max-w-56">
                                Sampai
                            </label>
                            <input class="input" name="end_date" placeholder="mm/dd/yyyy" type="text" readonly="true"
                                value="{{ $data['permit']['formatted_end_date'] }}" />
                        </div>
                    </div>
                    <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                        <label class="form-label max-w-56">
                            Karyawan
                        </label>
                        <input class="input" name="name" placeholder="Nama" type="input" readonly="true"
                            value="{{ $data['permit']['employee']['name'] }}" />
                    </div>
                    <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                        <label class="form-label max-w-56">
                            Catatan
                        </label>
                        <textarea class="textarea" name="note" placeholder="Catatan Izin" rows="3" type="text" readonly="true" />{{ $data['permit']['note'] }}</textarea>
                    </div>
                    @if ($data['permit']['permit_type']->value === PermitType::SAKIT->value && !empty($data['permit']['url_image']))
                        <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                            <label class="form-label max-w-56">Surat Sakit</label>
                            <a href="{{ asset($data['permit']['url_image']) }}" target="_blank" rel="noopener noreferrer">
                                <img src="{{ asset($data['permit']['url_image']) }}" alt="Surat Sakit"
                                    class="w-40 h-40 rounded-lg border border-gray-300 shadow hover:opacity-80 transition duration-200">
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="container-fixed mt-5">
        <div class="grid gap-5  mx-auto">
            <div class="card pb-2.5">
                <div class="card-header">
                    <h3 class="card-title">
                        Log Izin
                    </h3>
                </div>
                <div class="card-body">
                    <div class="flex flex-col">
                        @if ($data['permit']['known_at'])
                            <div class="flex items-start relative">
                                <div class="w-9 left-0 top-9 absolute bottom-0 translate-x-1/2 border-l border-l-gray-300">
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
                                                {{ $data['permit']->knownBy->name }}
                                            </a>
                                        </div>
                                        <span class="text-xs text-gray-600">
                                            {{ $data['permit']['formatted_known_at'] }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if ($data['permit']['approved_at'])
                            <div class="flex items-start relative">
                                <div class="w-9 left-0 top-9 absolute bottom-0 translate-x-1/2 border-l border-l-gray-300">
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
                                                {{ $data['permit']->approvedBy->name }}
                                            </a>
                                        </div>
                                        <span class="text-xs text-gray-600">
                                            {{ $data['permit']['formatted_approved_at'] }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-start relative">
                                <div class="w-9 left-0 top-9 absolute bottom-0 translate-x-1/2 border-l border-l-gray-300">
                                </div>
                                <div
                                    class="flex items-center justify-center shrink-0 rounded-full bg-gray-100 border border-gray-300 size-9 text-gray-600">
                                    <i class="ki-filled ki-check-circle text-success">
                                    </i>
                                </div>
                                <div class="pl-2.5 mb-7 text-md grow">
                                    <div class="flex flex-col">
                                        <div class="text-sm text-gray-800">
                                            Izin Disetujui
                                        </div>
                                        <span class="text-xs text-gray-600">
                                            {{ $data['permit']['formatted_approved_at'] }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @endif
                        @if ($data['permit']['status']->value === PermitStatus::DITOLAK->value)
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
                                                {{ $data['permit']->rejectBy->name }}
                                            </a>
                                        </div>
                                        <span class="text-xs text-gray-600">
                                            {{ $data['permit']['formatted_updated_at'] }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if ($data['permit']['status']->value === PermitStatus::DIBATALKAN->value)
                            <div class="flex items-start relative">
                                <div
                                    class="flex items-center justify-center shrink-0 rounded-full bg-gray-100 border border-gray-300 size-9 text-gray-600">
                                    <i class="ki-filled ki-file-right text-warning">
                                    </i>
                                </div>
                                <div class="pl-2.5 text-md grow">
                                    <div class="flex flex-col">
                                        <div class="text-sm text-gray-800">
                                            Izin Dibatalkan
                                        </div>
                                        <span class="text-xs text-gray-600">
                                            {{ $data['permit']['formatted_updated_at'] }}
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
    <!-- End of Container -->
@endsection


@push('javascript')
    <script type="text/javascript">
        function confirmPermitApproval(event) {
            if (!confirm("Are you sure you want to approve this permit?")) {
                event.preventDefault(); // Prevent form submission if user cancels
                return false;
            }
            return true; // Allow form submission if user confirms
        }

        function rejectPermit(event) {
            if (!confirm("Are you sure you want to reject this permit?")) {
                event.preventDefault(); // Prevent form submission if user cancels
                return false;
            }
            return true; // Allow form submission if user confirms
        }
    </script>
@endpush
