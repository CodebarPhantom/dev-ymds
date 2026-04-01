<header class="header fixed top-0 z-10 left-0 right-0 flex items-stretch shrink-0 bg-[#fefefe] dark:bg-coal-500"
    data-sticky="true" data-sticky-class="shadow-sm dark:border-b dark:border-b-coal-100" data-sticky-name="header"
    id="header">
    <!-- begin: container -->
    <div class="container-fixed flex justify-between items-stretch lg:gap-4" id="header_container">
        <div class="flex gap-1 lg:hidden items-center -ml-1">
            <a class="shrink-0" href="{{ url('/') }}">
                <img class="max-h-[25px] w-full" src="{{ asset('storage/logo.jpeg') }}" />
            </a>
            <div class="flex items-center">
                <button class="btn btn-icon btn-light btn-clear btn-sm" data-drawer-toggle="#sidebar">
                    <i class="ki-filled ki-menu">
                    </i>
                </button>
            </div>
        </div>
        <!-- Breadcrumbs -->
        <div class="flex [.header_&]:below-lg:hidden items-center gap-1.25 text-xs lg:text-sm font-medium mb-2.5 lg:mb-0"
            data-reparent="true" data-reparent-mode="prepend|lg:prepend"
            data-reparent-target="#content_container|lg:#header_container">
            @foreach ($data['breadcrumbs'] as $index => $breadcrumb)
                <span class="text-gray-800">{{ $breadcrumb }}</span>

                @if (isset($data['breadcrumbs'][$index + 1]))
                    <i class="ki-filled ki-right text-gray-500 text-3xs"></i>
                @endif
            @endforeach
        </div>
        <!-- End of Breadcrumbs -->
        <div class="flex items-center gap-2 lg:gap-3.5">

            <div class="dropdown" data-dropdown="true" data-dropdown-offset="70px, 10px"
                data-dropdown-placement="bottom-end" data-dropdown-trigger="click|lg:click">
                {{-- <button
                    class="dropdown-toggle btn btn-icon btn-icon-lg relative cursor-pointer size-9 rounded-full hover:bg-primary-light hover:text-primary dropdown-open:bg-primary-light dropdown-open:text-primary text-gray-500">
                    <i class="ki-filled ki-notification-on">
                    </i>
                    @if ($hasUnreadNotificationAlls || $hasNotificationForMe || $hasNotificationDepartment)
                        <span
                            class="badge badge-dot badge-danger size-[5px] absolute top-0.5 right-0.5 transform translate-y-1/2">
                        </span>
                    @endif
                </button> --}}
                {{-- <div class="dropdown-content light:border-gray-300 w-full max-w-[460px]">
                    <div class="flex items-center justify-between gap-2.5 text-sm text-gray-900 font-semibold px-5 py-2.5"
                        id="notifications_header">
                        Notifikasi
                        <button class="btn btn-sm btn-icon btn-light btn-clear shrink-0" data-dropdown-dismiss="true">
                            <i class="ki-filled ki-cross">
                            </i>
                        </button>

                    </div>
                        @php
                            // dd( Auth::user()->load('employee'))
                        @endphp
                    <div class="border-b border-b-gray-200">
                    </div>
                    <div class="tabs justify-between px-5 mb-2" data-tabs="true" id="notifications_tabs">
                        <div class="flex items-center gap-5">
                            <button class="tab active relative" data-tab-toggle="#notifications_tab_all">
                                Semua
                                @if ($hasUnreadNotificationAlls)
                                    <span
                                        class="badge badge-dot badge-danger size-[5px] absolute top-2 right-0 transform translate-y-1/2 translate-x-full">
                                    </span>
                                @endif
                            </button>

                            <button class="tab relative" data-tab-toggle="#notifications_tab_for_me">
                                Untuk Saya
                                @if ($hasNotificationForMe)
                                    <span
                                        class="badge badge-dot badge-danger size-[5px] absolute top-2 right-0 transform translate-y-1/2 translate-x-full">
                                    </span>
                                @endif
                            </button>
                            <button class="tab relative" data-tab-toggle="#notifications_tab_team">
                                Tim
                                @if ($hasNotificationDepartment)
                                    <span
                                        class="badge badge-dot badge-danger size-[5px] absolute top-2 right-0 transform translate-y-1/2 translate-x-full">
                                    </span>
                                @endif
                            </button>
                        </div>
                    </div>
                    <div class="grow" id="notifications_tab_all">
                        <div class="flex flex-col">
                            <div class="scrollable-y-auto" data-scrollable="true" data-scrollable-dependencies="#header"
                                data-scrollable-max-height="auto" data-scrollable-offset="200px">
                                <div class="flex flex-col gap-5 pt-3 pb-4 divider-y divider-gray-200">
                                    @foreach ($notificationAlls as $allNotification)
                                        <div class="flex items-center grow gap-2.5 px-5 ">
                                            @if (!$allNotification->is_read)
                                                <div
                                                    class="flex items-center justify-center size-8 bg-warning-light rounded-full border border-success-clarity">
                                                    <i class="ki-filled ki-information-2 text-warning text-lg">
                                                    </i>
                                                </div>
                                            @else
                                                <div
                                                    class="flex items-center justify-center size-8 bg-success-light rounded-full border border-success-clarity">
                                                    <i class="ki-filled ki-check text-lg text-success">
                                                    </i>
                                                </div>
                                            @endif
                                            <div class="flex flex-col gap-1">
                                                <div class="flex gap-1">
                                                    <span class="text-2sm font-medium text-gray-700">
                                                        {{ $allNotification->title }}
                                                    </span>
                                                    <span class="flex items-center text-2xs font-medium text-gray-500">
                                                        <span class="badge badge-circle bg-gray-500 size-1 mx-1.5">
                                                        </span>
                                                        {{ $allNotification->created_at->diffForHumans() }}
                                                    </span>
                                                </div>

                                                <span class="flex items-center text-2xs font-medium text-gray-500">
                                                    {{ $allNotification->description }}
                                                </span>

                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            <div class="border-b border-b-gray-200">
                            </div>
                            <div class="grid p-5 gap-2.5" id="notifications_all_footer">
                                <button class="btn btn-sm btn-light justify-center markAllReadBtn">
                                    Tandai Semua telah dibaca
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="grow hidden" id="notifications_tab_for_me">
                        <div class="flex flex-col">
                            <div class="scrollable-y-auto" data-scrollable="true" data-scrollable-dependencies="#header"
                                data-scrollable-max-height="auto" data-scrollable-offset="200px">
                                <div class="flex flex-col gap-5 pt-3 pb-4">
                                    @foreach ($notificationForMes as $notificationForMe)
                                        <div class="flex items-center grow gap-2.5 px-5">
                                            @if (!$notificationForMe->is_read)
                                                <div
                                                    class="flex items-center justify-center size-8 bg-warning-light rounded-full border border-success-clarity">
                                                    <i class="ki-filled ki-information-2 text-warning text-lg">
                                                    </i>
                                                </div>
                                            @else
                                                <div
                                                    class="flex items-center justify-center size-8 bg-success-light rounded-full border border-success-clarity">
                                                    <i class="ki-filled ki-check text-lg text-success">
                                                    </i>
                                                </div>
                                            @endif
                                            <div class="flex flex-col gap-1">
                                                <div class="flex gap-1">
                                                    <span class="text-2sm font-medium text-gray-700">
                                                        {{ $notificationForMe->title }}
                                                    </span>
                                                    <span class="flex items-center text-2xs font-medium text-gray-500">
                                                        <span class="badge badge-circle bg-gray-500 size-1 mx-1.5">
                                                        </span>
                                                        {{ $notificationForMe->created_at->diffForHumans() }}
                                                    </span>
                                                </div>

                                                <span class="flex items-center text-2xs font-medium text-gray-500">
                                                    {{ $notificationForMe->description }}
                                                </span>

                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            <div class="border-b border-b-gray-200">
                            </div>
                            <div class="grid p-5 gap-2.5" id="notifications_inbox_footer">
                                <button class="btn btn-sm btn-light justify-center markAllReadBtn">
                                    Tandai Semua telah dibaca
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="grow hidden" id="notifications_tab_team">
                        <div class="flex flex-col">
                            <div class="scrollable-y-auto" data-scrollable="true"
                                data-scrollable-dependencies="#header" data-scrollable-max-height="auto"
                                data-scrollable-offset="200px">
                                <div class="flex flex-col gap-5 pt-3 pb-4">
                                    @foreach ($notificationDepartments as $notificationDepartment)
                                        <div class="flex items-center grow gap-2.5 px-5">
                                            @if (!$notificationDepartment->is_read)
                                                <div
                                                    class="flex items-center justify-center size-8 bg-warning-light rounded-full border border-success-clarity">
                                                    <i class="ki-filled ki-information-2 text-warning text-lg">
                                                    </i>
                                                </div>
                                            @else
                                                <div
                                                    class="flex items-center justify-center size-8 bg-success-light rounded-full border border-success-clarity">
                                                    <i class="ki-filled ki-check text-lg text-success">
                                                    </i>
                                                </div>
                                            @endif
                                            <div class="flex flex-col gap-1">
                                                <div class="flex gap-1">
                                                    <span class="text-2sm font-medium text-gray-700">
                                                        {{ $notificationDepartment->title }}
                                                    </span>
                                                    <span class="flex items-center text-2xs font-medium text-gray-500">
                                                        <span class="badge badge-circle bg-gray-500 size-1 mx-1.5">
                                                        </span>
                                                        {{ $notificationDepartment->created_at->diffForHumans() }}
                                                    </span>
                                                </div>

                                                <span class="flex items-center text-2xs font-medium text-gray-500">
                                                    {{ $notificationDepartment->description }}
                                                </span>

                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            <div class="border-b border-b-gray-200">
                            </div>
                            <div class="grid p-5 gap-2.5" id="notifications_team_footer">
                                <button class="btn btn-sm btn-light justify-center markAllReadBtn">
                                    Tandai Semua telah dibaca
                                </button>
                            </div>
                        </div>
                    </div>
                </div> --}}
            </div>
            <div class="menu" data-menu="true">
                <div class="menu-item" data-menu-item-offset="20px, 10px" data-menu-item-placement="bottom-end"
                    data-menu-item-toggle="dropdown" data-menu-item-trigger="click|lg:click">
                    <div class="menu-toggle btn btn-icon rounded-full">
                        <img alt="" class="size-9 rounded-full border-2 border-success shrink-0"
                            src="{{ Auth::user()->url_image !== null ? url('/' . Auth::user()->url_image) : asset('assets/media/avatars/blank.png') }}" />
                    </div>
                    <div class="menu-dropdown menu-default light:border-gray-300 w-full max-w-[250px]">
                        <div class="flex items-center justify-between px-5 py-1.5 gap-1.5">
                            <div class="flex items-center gap-2">
                                <img alt="" class="size-9 rounded-full border-2 border-success"
                                    src="{{ Auth::user()->url_image !== null ? url('/' . Auth::user()->url_image) : asset('assets/media/avatars/blank.png') }}" />
                                <div class="flex flex-col gap-1.5">
                                    <span class="text-sm text-gray-800 font-semibold leading-none">
                                        {{ Auth::user()->name }}
                                    </span>
                                    <a class="text-xs text-gray-600 hover:text-primary font-medium leading-none"
                                        href="html/demo1/account/home/get-started.html">
                                        {{ Auth::user()->email }}
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="menu-separator">
                        </div>
                        <div class="flex flex-col">
                            {{-- <div class="menu-item mb-0.5">
                                <div class="menu-link">
                                    <span class="menu-icon">
                                        <i class="ki-filled ki-moon">
                                        </i>
                                    </span>
                                    <span class="menu-title">
                                        Dark Mode
                                    </span>
                                    <label class="switch switch-sm">
                                        <input data-theme-state="dark" data-theme-toggle="true" name="check"
                                            type="checkbox" value="1" />
                                    </label>
                                </div>
                            </div> --}}
                            <div class="menu-item px-4 py-1.5">
                                <a class="btn btn-sm btn-light justify-center"
                                    onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                    Log out
                                </a>
                                <form id="logout-form" action="{{ route('logout') }}" method="POST"
                                    style="display: none;">
                                    @csrf
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- end: container -->
</header>
