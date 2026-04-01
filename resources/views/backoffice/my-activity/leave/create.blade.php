@extends('layouts.main')

@section('content')
    <!-- Container -->
    @include('backoffice.my-activity.partials.submenu')
    <!-- Container -->
    <form action="{{ route('my-activity.my-leave.store') }}" method="post">
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
                        <button class="btn btn-sm text-center bg-[#4b5563] text-white">
                            <i class="ki-filled ki-calendar"></i> &nbsp; Tersisa {{ $data['remainingLeave'] }} Hari Cuti
                         </button>
                        <a class="btn text-center btn-sm btn-primary" href="{{ route('my-activity.my-leave.index') }}">
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
                        <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                            <label class="form-label max-w-56">
                                Dari Tanggal
                            </label>
                            <input class="input" name="start_date" placeholder="mm/dd/yyyy" type="date" value="" />
                        </div>
                        <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                            <label class="form-label max-w-56">
                                Sampai Tanggal
                            </label>
                            <input class="input" name="end_date" placeholder="mm/dd/yyyy" type="date" value="" />
                        </div>
                        <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                            <label class="form-label max-w-56">
                                Catatan
                            </label>
                            <textarea class="textarea" name="note" placeholder="Catatan cuti" rows="3" type="text" value="" /></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <!-- End of Container -->
@endsection
