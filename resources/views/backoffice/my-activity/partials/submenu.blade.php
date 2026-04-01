<div
    class="flex items-center flex-wrap md:flex-nowrap lg:items-end justify-between border-b border-b-gray-200 dark:border-b-coal-100 gap-3 lg:gap-6 mb-5 lg:mb-10">
    <!-- Container -->
    <div class="container-fixed" id="hero_container">
        <div class="grid">
            <div class="scrollable-x-auto">
                <div class="menu gap-3" data-menu="true">
                    <div
                        class="menu-item border-b-2 border-b-transparent menu-item-active:border-b-primary menu-item-here:border-b-primary {{ request()->is('my-activity/my-leave*') ? 'active' : '' }}">
                        <a class="menu-link gap-1.5 pb-2 lg:pb-4 px-2" href="{{ route('my-activity.my-leave.index') }}" tabindex="0">
                            <span
                                class="menu-title text-nowrap font-medium text-sm text-gray-700 menu-item-active:text-primary menu-item-active:font-semibold menu-item-here:text-primary menu-item-here:font-semibold menu-item-show:text-primary menu-link-hover:text-primary">
                                Cuti
                            </span>
                        </a>
                    </div>
                    {{-- <div
                        class="menu-item border-b-2 border-b-transparent menu-item-active:border-b-primary menu-item-here:border-b-primary {{ request()->is('company*') ? 'active' : '' }}  ">
                        <a class="menu-link gap-1.5 pb-2 lg:pb-4 px-2" href="{{ route('company.index') }}"
                            tabindex="0">
                            <span
                                class="menu-title text-nowrap font-medium text-sm text-gray-700 menu-item-active:text-primary menu-item-active:font-semibold menu-item-here:text-primary menu-item-here:font-semibold menu-item-show:text-primary menu-link-hover:text-primary">
                                Dinas Luar
                            </span>
                        </a>
                    </div> --}}
                    <div
                        class="menu-item border-b-2 border-b-transparent menu-item-active:border-b-primary menu-item-here:border-b-primary {{ request()->is('my-activity/my-permit*') ? 'active' : '' }} ">
                        <a class="menu-link gap-1.5 pb-2 lg:pb-4 px-2" href="{{ route('my-activity.my-permit.index') }}"
                            tabindex="0">
                            <span
                                class="menu-title text-nowrap font-medium text-sm text-gray-700 menu-item-active:text-primary menu-item-active:font-semibold menu-item-here:text-primary menu-item-here:font-semibold menu-item-show:text-primary menu-link-hover:text-primary">
                                Izin
                            </span>
                        </a>
                    </div>
                    {{-- <div
                        class="menu-item border-b-2 border-b-transparent menu-item-active:border-b-primary menu-item-here:border-b-primary {{ request()->is('departement*') ? 'active' : '' }}">
                        <a class="menu-link gap-1.5 pb-2 lg:pb-4 px-2" href="{{ route('departement.index') }}"
                            tabindex="0">
                            <span
                                class="menu-title text-nowrap font-medium text-sm text-gray-700 menu-item-active:text-primary menu-item-active:font-semibold menu-item-here:text-primary menu-item-here:font-semibold menu-item-show:text-primary menu-link-hover:text-primary">
                                Kasbon
                            </span>
                        </a>
                    </div> --}}
                    <div
                        class="menu-item border-b-2 border-b-transparent menu-item-active:border-b-primary menu-item-here:border-b-primary {{ request()->is('my-activity/my-overtime*') ? 'active' : '' }}">
                        <a class="menu-link gap-1.5 pb-2 lg:pb-4 px-2" href="{{ route('my-activity.my-overtime.index') }}"
                            tabindex="0">
                            <span
                                class="menu-title text-nowrap font-medium text-sm text-gray-700 menu-item-active:text-primary menu-item-active:font-semibold menu-item-here:text-primary menu-item-here:font-semibold menu-item-show:text-primary menu-link-hover:text-primary">
                                Lembur
                            </span>
                        </a>
                    </div>
                    <div
                        class="menu-item border-b-2 border-b-transparent menu-item-active:border-b-primary menu-item-here:border-b-primary {{ request()->is('my-activity/my-business-trip*') ? 'active' : '' }}">
                        <a class="menu-link gap-1.5 pb-2 lg:pb-4 px-2" href="{{ route('my-activity.my-business-trip.index') }}"
                            tabindex="0">
                            <span
                                class="menu-title text-nowrap font-medium text-sm text-gray-700 menu-item-active:text-primary menu-item-active:font-semibold menu-item-here:text-primary menu-item-here:font-semibold menu-item-show:text-primary menu-link-hover:text-primary">
                                Perjalan Dinas
                            </span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End of Container -->
</div>
