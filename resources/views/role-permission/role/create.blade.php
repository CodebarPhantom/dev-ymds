@extends('layouts.main')

@section('content')
    <!-- Container -->
    @include('role-permission.partials.submenu')
    <!-- Container -->
    <form action="{{ route('roles.store') }}" method="post">
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
                        <a class="btn text-center btn-sm btn-primary" href="{{ route('roles.index') }}">
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

        <!-- Container -->
        <div class="container-fixed">
            <div class="flex grow gap-5 lg:gap-7.5">
                <div class="hidden lg:block w-[230px] shrink-0">
                    <div class="w-[230px]" data-sticky="true" data-sticky-animation="true"
                        data-sticky-class="fixed z-[4] left-auto top-[calc(var(--tw-header-height)+1.875rem)]"
                        data-sticky-name="scrollspy" data-sticky-offset="200">
                        <div class="flex flex-col grow relative before:absolute before:left-[11px] before:top-0 before:bottom-0 before:border-l before:border-gray-200"
                            data-scrollspy="true" data-scrollspy-offset="80px|lg:110px" data-scrollspy-target="body">
                            <a class="flex items-center rounded-lg pl-2.5 pr-2.5 py-2.5 gap-1.5 active border border-transparent text-2sm text-gray-800 hover:text-primary hover:font-medium scrollspy-active:bg-secondary-active scrollspy-active:text-primary scrollspy-active:font-medium dark:hover:bg-coal-300 dark:hover:border-gray-100 hover:rounded-lg dark:scrollspy-active:bg-coal-300 dark:scrollspy-active:border-gray-100"
                                data-scrollspy-anchor="true" href="#basic_settings">
                                <span
                                    class="flex w-1.5 relative before:absolute before:top-0 before:left-px before:size-1.5 before:rounded-full before:-translate-x-2/4 before:-translate-y-2/4 scrollspy-active:before:bg-primary">
                                </span>
                                Informasi Jabatan
                            </a>
                            <div class="flex flex-col">
                                <div class="pl-6 pr-2.5 py-2.5 text-2sm font-semibold text-gray-900">
                                    Grup Hak Akses
                                </div>
                                <div class="flex flex-col">
                                    @foreach ($data['permissionGroupWithPermissions'] as $permissionGroupWithPermission)
                                        <a class="flex items-center rounded-lg pl-2.5 pr-2.5 py-2.5 gap-3.5 border border-transparent text-2sm text-gray-800 hover:text-primary hover:font-medium scrollspy-active:bg-secondary-active scrollspy-active:text-primary scrollspy-active:font-medium dark:hover:bg-coal-300 dark:hover:border-gray-100 hover:rounded-lg dark:scrollspy-active:bg-coal-300 dark:scrollspy-active:border-gray-100"
                                            data-scrollspy-anchor="true" href="#{{ $permissionGroupWithPermission->slug }}">
                                            <span
                                                class="flex w-1.5 relative before:absolute before:top-0 before:left-px before:size-1.5 before:rounded-full before:-translate-x-2/4 before:-translate-y-2/4 scrollspy-active:before:bg-primary">
                                            </span>
                                            {{ $permissionGroupWithPermission->name }}
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="flex flex-col items-stretch grow gap-5 lg:gap-7.5">
                    <div class="card pb-2.5">
                        <div class="card-header" id="basic_settings">
                            <h3 class="card-title">
                                Informasi Jabatan
                            </h3>
                            <div class="flex gap-12">
                                <label class="form-label flex items-center gap-2.5 text-nowrap">
                                    <label for="global-check-all" class="ml-2">Checklist Semua </label>
                                    <input class="checkbox" id="global-check-all" type="checkbox"  />
                                </label>
                            </div>
                        </div>
                        <div class="card-body grid gap-5">
                            <div class="w-full">
                                <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                                    <label class="form-label flex items-center gap-1 max-w-56">
                                        Name
                                    </label>
                                    <input class="input" type="text" name="name" placeholder="Nama Jabatan" />
                                </div>
                            </div>
                            <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                                <label class="form-label max-w-56">
                                    is Active
                                </label>
                                <div class="flex gap-12">
                                    <label class="form-label flex items-center gap-2.5 text-nowrap">
                                        <input checked="true" class="radio " name="is_active" type="radio"
                                            value="1" />
                                        Active
                                    </label>
                                    <label class="form-label flex items-center gap-2.5 text-nowrap">
                                        <input class="radio" name="is_active" type="radio" value="0" />
                                        Inactive
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    @foreach ($data['permissionGroupWithPermissions'] as $permissionGroupWithPermission)
                        <div class="card pb-2.5">
                            <div class="card-header flex justify-between items-center"
                                id="{{ $permissionGroupWithPermission->slug }}">
                                <h3 class="card-title">
                                    {{ $permissionGroupWithPermission->name }}
                                </h3>
                                <div class="flex gap-12">
                                    <label class="form-label flex items-center gap-2.5 text-nowrap">
                                        <label for="master-check-{{ $permissionGroupWithPermission->slug }}">Checklist {{ $permissionGroupWithPermission->name }}</label>
                                        <input class="checkbox master-check" id="master-check-{{ $permissionGroupWithPermission->slug }}"  data-group-id="{{ $permissionGroupWithPermission->slug }}" type="checkbox"  />
                                    </label>
                                </div>
                            </div>

                            <div class="card-body grid gap-5 pt-2.5">
                                <div class="w-full">
                                    <div class="grid gap-2">
                                        @foreach ($permissionGroupWithPermission->permissions as $permission)
                                            <div
                                                class="flex items-center justify-between flex-wrap border border-gray-200 rounded-xl gap-2 p-3.5">
                                                <div class="flex items-center flex-wrap">
                                                    <div class="flex flex-col">
                                                        <span class="text-gray-700">
                                                            {{ Str::of($permission->name)->replace('-', ' ')->title() }}
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="flex items-center gap-2 lg:gap-5">
                                                    <label class="switch switch-lg">
                                                        <input type="checkbox" class="permission-check" value="{{ $permission->name }}" name="permissions[]"
                                                            data-group-id="{{ $permissionGroupWithPermission->slug }}">
                                                    </label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach

                </div>
            </div>
        </div>
        <!-- End of Container -->
    </form>

    <!-- End of Container -->
@endsection

@push('javascript')
    <script type="text/javascript">
        document.addEventListener('DOMContentLoaded', function() {
            // Handle the global "Check All" checkbox
            const globalCheckAll = document.getElementById('global-check-all');
            const masterCheckboxes = document.querySelectorAll('.master-check');
            const permissionCheckboxes = document.querySelectorAll('.permission-check');

            // Function to check/uncheck all permissions
            globalCheckAll.addEventListener('change', function() {
                const isChecked = this.checked;

                // Toggle all individual permission checkboxes
                permissionCheckboxes.forEach(permissionCheckbox => {
                    permissionCheckbox.checked = isChecked;
                });

                // Toggle all group checkboxes
                masterCheckboxes.forEach(masterCheckbox => {
                    masterCheckbox.checked = isChecked;
                });
            });

            // Handle the master "Check All" checkbox in each group
            masterCheckboxes.forEach(masterCheckbox => {
                masterCheckbox.addEventListener('change', function() {
                    const groupId = this.getAttribute('data-group-id');
                    const groupPermissions = document.querySelectorAll(
                        `.permission-check[data-group-id="${groupId}"]`);

                    // Set the checked status of all checkboxes in this group
                    groupPermissions.forEach(checkbox => {
                        checkbox.checked = this.checked;
                    });

                    // Update global checkbox state based on all checkboxes
                    updateGlobalCheckAllState();
                });
            });

            // Handle individual permission checkboxes
            permissionCheckboxes.forEach(permissionCheckbox => {
                permissionCheckbox.addEventListener('change', function() {
                    const groupId = this.getAttribute('data-group-id');
                    const groupPermissions = document.querySelectorAll(
                        `.permission-check[data-group-id="${groupId}"]`);
                    const masterCheckbox = document.querySelector(`#master-check-${groupId}`);

                    // If any checkbox in the group is unchecked, uncheck the group checkbox
                    if (![...groupPermissions].every(permission => permission.checked)) {
                        masterCheckbox.checked = false;
                    } else {
                        // If all checkboxes in the group are checked, check the group checkbox
                        masterCheckbox.checked = true;
                    }

                    // Update global checkbox state based on all checkboxes
                    updateGlobalCheckAllState();
                });
            });

            // Function to update the state of the global "Check All" checkbox
            function updateGlobalCheckAllState() {
                if (![...permissionCheckboxes].every(permission => permission.checked)) {
                    globalCheckAll.checked = false;
                } else {
                    globalCheckAll.checked = true;
                }
            }
        });
        document.addEventListener('DOMContentLoaded', function() {
            // Handle the global "Check All" checkbox
            const globalCheckAll = document.getElementById('global-check-all');
            const masterCheckboxes = document.querySelectorAll('.master-check');
            const permissionCheckboxes = document.querySelectorAll('.permission-check');

            // Function to check/uncheck all permissions
            globalCheckAll.addEventListener('change', function() {
                const isChecked = this.checked;

                // Toggle all individual permission checkboxes
                permissionCheckboxes.forEach(permissionCheckbox => {
                    permissionCheckbox.checked = isChecked;
                });

                // Toggle all group checkboxes
                masterCheckboxes.forEach(masterCheckbox => {
                    masterCheckbox.checked = isChecked;
                });
            });

            // Handle the master "Check All" checkbox in each group
            masterCheckboxes.forEach(masterCheckbox => {
                masterCheckbox.addEventListener('change', function() {
                    const groupId = this.getAttribute('data-group-id');
                    const groupPermissions = document.querySelectorAll(
                        `.permission-check[data-group-id="${groupId}"]`);

                    // Set the checked status of all checkboxes in this group
                    groupPermissions.forEach(checkbox => {
                        checkbox.checked = this.checked;
                    });

                    // Update global checkbox state based on all checkboxes
                    updateGlobalCheckAllState();
                });
            });

            // Handle individual permission checkboxes
            permissionCheckboxes.forEach(permissionCheckbox => {
                permissionCheckbox.addEventListener('change', function() {
                    const groupId = this.getAttribute('data-group-id');
                    const groupPermissions = document.querySelectorAll(
                        `.permission-check[data-group-id="${groupId}"]`);
                    const masterCheckbox = document.querySelector(`#master-check-${groupId}`);

                    // If any checkbox in the group is unchecked, uncheck the group checkbox
                    if (![...groupPermissions].every(permission => permission.checked)) {
                        masterCheckbox.checked = false;
                    } else {
                        // If all checkboxes in the group are checked, check the group checkbox
                        masterCheckbox.checked = true;
                    }

                    // Update global checkbox state based on all checkboxes
                    updateGlobalCheckAllState();
                });
            });

            // Function to update the state of the global "Check All" checkbox
            function updateGlobalCheckAllState() {
                if (![...permissionCheckboxes].every(permission => permission.checked)) {
                    globalCheckAll.checked = false;
                } else {
                    globalCheckAll.checked = true;
                }
            }
        });
    </script>
@endpush
