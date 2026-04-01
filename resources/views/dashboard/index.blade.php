@extends('layouts.main')

@section('content')
    <div class="container-fixed">
        <div class="flex flex-wrap items-center lg:items-end justify-between gap-5 pb-7.5">
            <div class="flex flex-col justify-center gap-2">
                <h1 class="text-xl font-bold leading-none text-gray-900">
                    {{ $data['pageTitle'] }}
                </h1>
                {{-- <div class="flex items-center gap-2 text-sm font-normal text-gray-700">
                Central Hub for Personal Customization
            </div> --}}
            </div>
        </div>
    </div>
    <div class="container-fixed">
        <!-- Welcome Section -->
        <div class="flex flex-col justify-center gap-1 rounded-lg p-4 bg-primary-light pb-7.5 mb-6">
            <h3 class="text-md leading-none font-semibold text-gray-900">
                Welcome !
            </h3>
            <p class="text-gray-700 text-2sm font-normal">
                Selamat Datang {{ Auth::User()->name }}
            </p>
        </div>
    </div>

    {{-- <div class="container-fixed flex flex-col items-center justify-center h-1/2 mt-2">
        <img src="{{ asset('assets/media/illustrations/22.svg') }}" alt="Dashboard Coming Soon" class="w-1/2 h-1/2">
        <h3 class="text-lg font-semibold text-gray-900 mt-4">
            Coming Soon!
        </h3>
    </div> --}}
@endsection

@push('javascript')
    <script src="https://cdn.jsdelivr.net/npm/dayjs@1/dayjs.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dayjs/1.9.6/plugin/isBetween.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
@endpush
