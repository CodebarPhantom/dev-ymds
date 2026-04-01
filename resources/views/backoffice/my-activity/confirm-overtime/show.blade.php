@extends('layouts.main')

@section('content')
    @php
        use App\Enums\OvertimeStatus;
    @endphp

    <!-- Container -->
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
                <a class="btn text-center btn-sm btn-primary"
                    href="{{ route('my-activity.my-overtime-confirm.confirm-overtime-index') }}">
                    <i class="ki-filled ki-left"></i></i>Kembali
                </a>
                @if ($data['overtime']['status']->value === 1)
                    <form action="{{ route('my-activity.my-overtime-confirm.confirm-overtime-update', $data['overtime']['id']) }}"
                        method="POST" onsubmit="return confirmOvertimeApproval(event)" class="inline">
                        @csrf
                        <input type="hidden" name="_method" value="PUT">
                        <button type="submit" class="btn text-center btn-sm btn-success" data-tooltip="#confirmLeave">
                            <i class="ki-filled ki-check"></i> Setujui Lembur
                        </button>
                    </form>

                    <form action="{{ route('my-activity.my-overtime-confirm.reject-overtime-update', $data['overtime']['id']) }}"
                        method="POST" onsubmit="return rejectOvertime(event)" class="inline">
                        @csrf
                        <input type="hidden" name="_method" value="PUT">
                        <button type="submit" class="btn text-center btn-sm btn-danger" data-tooltip="#rejectOvertime">
                            <i class="ki-filled ki-cross"></i> Tolak Lembur
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
                                Nama
                            </label>
                            <input class="input" name="name" placeholder="mm/dd/yyyy" type="text" readonly="true"
                                value="{{ $data['overtime']['employee']['name'] }}" />
                        </div>
                        <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                            <label class="form-label max-w-56">
                                Status
                            </label>
                            <span class="badge badge-pill badge-outline badge-{{ $data['overtime']['status_color'] }}">
                                {{ $data['overtime']['status_label'] }}
                            </span>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                            <label class="form-label max-w-56">
                                Dari
                            </label>
                            <input readonly="true" class="input" name="start_date" type="input" value="{{ $data['overtime']['formatted_start_date'] }}" />
                        </div>
                        <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                            <label class="form-label max-w-56">
                                Sampai
                            </label>
                            <input readonly="true" class="input" name="end_date" type="input" value="{{ $data['overtime']['formatted_end_date'] }}" />
                        </div>
                    </div>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                            <label class="form-label max-w-56">
                                Shift
                            </label>
                            <input readonly="true" class="input" name="shift" type="text"
                                value="{{ $data['overtime']['shift']['name'] }}" />
                        </div>
                        <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                            <label class="form-label max-w-56">
                                Durasi Lembur
                            </label>
                            <input readonly="true" class="input" name="overtime_hour" placeholder="Durasi Lembur"
                                type="number" min="1"
                                value="{{ old('overtime_hour', $data['overtime']['overtime_hour']) }}" />
                        </div>
                    </div>
                    <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                        <label class="form-label max-w-56">
                            Nominal Lembur
                        </label>
                        <input readonly="true" class="input" name="shift" type="text"
                            value="{{ number_format($data['overtime']['overtime_pay'], 0) }}" />
                    </div>

                    <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                        <label class="form-label max-w-56">
                            Catatan
                        </label>
                        <textarea class="textarea" name="note" placeholder="Catatan lembur" rows="3" type="text" readonly />{{ $data['overtime']['note'] }}</textarea>
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
                        Log Lembur
                    </h3>
                </div>
                <div class="card-body">
                    <div class="flex flex-col">
                        @if ($data['overtime']['known_at'])
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
                                                {{ $data['overtime']->knownBy->name }}
                                            </a>
                                        </div>
                                        <span class="text-xs text-gray-600">
                                            {{ $data['overtime']['formatted_known_at'] }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if ($data['overtime']['approved_at'])
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
                                                {{ $data['overtime']->approvedBy->name }}
                                            </a>
                                        </div>
                                        <span class="text-xs text-gray-600">
                                            {{ $data['overtime']['formatted_approved_at'] }}
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
                                            Lembur Disetujui
                                        </div>
                                        <span class="text-xs text-gray-600">
                                            {{ $data['overtime']['formatted_approved_at'] }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @endif
                        @if ($data['overtime']['status']->value === OvertimeStatus::DITOLAK->value)
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
                                                {{ $data['overtime']->rejectBy->name }}
                                            </a>
                                        </div>
                                        <span class="text-xs text-gray-600">
                                            {{ $data['overtime']['formatted_updated_at'] }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if ($data['overtime']['status']->value === OvertimeStatus::DIBATALKAN->value)
                            <div class="flex items-start relative">
                                <div
                                    class="flex items-center justify-center shrink-0 rounded-full bg-gray-100 border border-gray-300 size-9 text-gray-600">
                                    <i class="ki-filled ki-file-right text-warning">
                                    </i>
                                </div>
                                <div class="pl-2.5 text-md grow">
                                    <div class="flex flex-col">
                                        <div class="text-sm text-gray-800">
                                            Lembur Dibatalkan
                                        </div>
                                        <span class="text-xs text-gray-600">
                                            {{ $data['overtime']['formatted_updated_at'] }}
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
        function confirmOvertimeApproval(event) {
            if (!confirm("Are you sure you want to approve this overtime?")) {
                event.preventDefault(); // Prevent form submission if user cancels
                return false;
            }
            return true; // Allow form submission if user confirms
        }

        function rejectOvertime(event) {
            if (!confirm("Are you sure you want to reject this overtime?")) {
                event.preventDefault(); // Prevent form submission if user cancels
                return false;
            }
            return true; // Allow form submission if user confirms
        }
    </script>
@endpush
