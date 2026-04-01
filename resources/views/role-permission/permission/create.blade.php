@extends('layouts.main')

@section('content')
    <!-- Container -->
    @include('role-permission.partials.submenu')
    <!-- Container -->
    <form action="{{ route('permissions.store') }}" method="post">
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
                        <a class="btn text-center btn-sm btn-primary" href="{{ route('permissions.index') }}">
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
                                Grup Hak Akses
                            </label>
                            <select class="select" name="permission_group_id" required>
                                <option value="" selected>--- Silahkan Pilih ---</option>
                                @foreach ($data['permissionGroups'] as $permissionGroup)
                                <option value="{{ $permissionGroup['id'] }}">
                                    {{ $permissionGroup['name'] }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                            <label class="form-label max-w-56">
                                Nama Hak Akses
                            </label>
                            <input class="input" name="name" placeholder="Nama Hak Akses" type="text" value="" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <!-- End of Container -->
@endsection
