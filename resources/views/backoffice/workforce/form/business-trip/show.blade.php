@extends('layouts.main')

@section('content')
    @php
        use App\Enums\BusinessTripStatus;

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
                <a class="btn text-center btn-sm btn-primary"
                    href="{{ route('workforce.submitted-form.business-trip.index') }}">
                    <i class="ki-filled ki-left"></i></i>Kembali
                </a>
                @if ($data['businessTrip']['status']->value === BusinessTripStatus::MENUNGGU_PERSETUJUAN_HR->value)
                    <form
                        action="{{ route('workforce.submitted-form.business-trip.confirm-business-trip-update', $data['businessTrip']['id']) }}"
                        method="POST" onsubmit="return confirmBusinessTripApproval(event)" class="inline">
                        @csrf
                        <input type="hidden" name="_method" value="PUT">
                        <button type="submit" class="btn text-center btn-sm btn-success" data-tooltip="#confirmBusinessTripApproval">
                            <i class="ki-filled ki-check"></i> Setujui Perjalanan Dinas
                        </button>
                    </form>


                    <form
                        action="{{ route('workforce.submitted-form.business-trip.reject-business-trip-update', $data['businessTrip']['id']) }}"
                        method="POST" onsubmit="return rejectBusinessTrip(event)" class="inline">
                        @csrf
                        <input type="hidden" name="_method" value="PUT">
                        <button type="submit" class="btn text-center btn-sm btn-danger" data-tooltip="#rejectBusinessTrip">
                            <i class="ki-filled ki-cross"></i> Tolak Perjalanan Dinas
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
                                Dari
                            </label>
                            <input class="input" name="start_date" placeholder="mm/dd/yyyy" type="text" readonly="true"
                                value="{{ $data['businessTrip']['formatted_start_date'] }}" />
                        </div>
                        <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                            <label class="form-label max-w-56">
                                Sampai
                            </label>
                            <input class="input" name="end_date" placeholder="mm/dd/yyyy" type="text" readonly="true"
                                value="{{ $data['businessTrip']['formatted_end_date'] }}" />
                        </div>
                    </div>
                    <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                        <label class="form-label max-w-56">
                            Karyawan
                        </label>
                        <input class="input" name="name" placeholder="Nama" type="input" readonly="true"
                            value="{{ $data['businessTrip']['employee']['name'] }}" />
                    </div>
                    <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                        <label class="form-label max-w-56">
                            Catatan
                        </label>
                        <textarea class="textarea" name="note" placeholder="Catatan Perjalanan Dinas" rows="3" type="text" readonly="true" />{{ $data['businessTrip']['note'] }}</textarea>
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
                        Log Perjalanan Dinas
                    </h3>
                </div>
                <div class="card-body">
                    <div class="flex flex-col">
                        @if ($data['businessTrip']['known_at'])
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
                                                {{ $data['businessTrip']->knownBy->name }}
                                            </a>
                                        </div>
                                        <span class="text-xs text-gray-600">
                                            {{ $data['businessTrip']['formatted_known_at'] }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if ($data['businessTrip']['approved_at'])
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
                                                {{ $data['businessTrip']->approvedBy->name }}
                                            </a>
                                        </div>
                                        <span class="text-xs text-gray-600">
                                            {{ $data['businessTrip']['formatted_approved_at'] }}
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
                                            Perjalanan Dinas Disetujui
                                        </div>
                                        <span class="text-xs text-gray-600">
                                            {{ $data['businessTrip']['formatted_approved_at'] }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @endif
                        @if ($data['businessTrip']['status']->value === BusinessTripStatus::DITOLAK->value)
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
                                                {{ $data['businessTrip']->rejectBy->name }}
                                            </a>
                                        </div>
                                        <span class="text-xs text-gray-600">
                                            {{ $data['businessTrip']['formatted_updated_at'] }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if ($data['businessTrip']['status']->value === BusinessTripStatus::DIBATALKAN->value)
                            <div class="flex items-start relative">
                                <div
                                    class="flex items-center justify-center shrink-0 rounded-full bg-gray-100 border border-gray-300 size-9 text-gray-600">
                                    <i class="ki-filled ki-file-right text-warning">
                                    </i>
                                </div>
                                <div class="pl-2.5 text-md grow">
                                    <div class="flex flex-col">
                                        <div class="text-sm text-gray-800">
                                            Perjalanan Dinas Dibatalkan
                                        </div>
                                        <span class="text-xs text-gray-600">
                                            {{ $data['businessTrip']['formatted_updated_at'] }}
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
        function confirmBusinessTripApproval(event) {
            if (!confirm("Are you sure you want to approve this business permit?")) {
                event.preventDefault(); // Prevent form submission if user cancels
                return false;
            }
            return true; // Allow form submission if user confirms
        }

        function rejectBusinessTrip(event) {
            if (!confirm("Are you sure you want to reject this business permit?")) {
                event.preventDefault(); // Prevent form submission if user cancels
                return false;
            }
            return true; // Allow form submission if user confirms
        }
    </script>
@endpush
