<div
    class="flex items-center flex-wrap md:flex-nowrap lg:items-end justify-between border-b border-b-gray-200 dark:border-b-coal-100 gap-3 lg:gap-6 mb-5 lg:mb-10">
    <!-- Container -->
    <div class="container-fixed" id="hero_container">
        <div class="grid">
            <div class="scrollable-x-auto">
                <div class="menu gap-3" data-menu="true">
                    @can('work-schedule-shift-read')
                    <div
                        class="menu-item border-b-2 border-b-transparent menu-item-active:border-b-primary menu-item-here:border-b-primary {{ request()->is('work-schedule/holiday*') ? 'active' : '' }}">
                        <a class="menu-link gap-1.5 pb-2 lg:pb-4 px-2" href="{{ route('work-schedule.holiday.index') }}" tabindex="0">
                            <span
                                class="menu-title text-nowrap font-medium text-sm text-gray-700 menu-item-active:text-primary menu-item-active:font-semibold menu-item-here:text-primary menu-item-here:font-semibold menu-item-show:text-primary menu-link-hover:text-primary">
                                Hari Libur
                            </span>
                        </a>
                    </div>
                    @endcan
                    @can('work-schedule-shift-read')
                        <div class="menu-item border-b-2 border-b-transparent menu-item-active:border-b-primary menu-item-here:border-b-primary menu-item-dropdown {{ request()->is('work-schedule/shift*') ? 'here' : '' }}"
                            data-menu-item-placement="bottom-start" data-menu-item-toggle="dropdown"
                            data-menu-item-trigger="click|lg:hover">

                                <div class="menu-link gap-1.5 pb-2 lg:pb-4 px-2" tabindex="0">
                                    <span
                                        class="menu-title text-nowrap text-sm text-gray-700 menu-item-active:text-primary menu-item-active:font-medium menu-item-here:text-primary menu-item-here:font-medium menu-item-show:text-primary menu-link-hover:text-primary">
                                        Kelola Shift
                                    </span>
                                    <span class="menu-arrow">
                                        <i
                                            class="ki-filled ki-down text-2xs text-gray-500 menu-item-active:text-primary menu-item-here:text-primary menu-item-show:text-primary menu-link-hover:text-primary">
                                        </i>
                                    </span>
                                </div>

                                <div class="menu-dropdown menu-default py-2 min-w-[200px]" style="">
                                    <div class="menu-item {{ request()->is('work-schedule/shift') || request()->is('work-schedule/shift/*') ? 'active' : '' }}">
                                        <a class="menu-link" href="{{ route('work-schedule.shift.index') }}" tabindex="0">
                                            <span class="menu-title">
                                                Shift
                                            </span>
                                        </a>
                                    </div>
                                    <div class="menu-item {{ request()->is('work-schedule/shift-fixed*') ? 'active' : '' }}">
                                        <a class="menu-link" href="{{ route('work-schedule.shift-fixed.index') }}" tabindex="0">
                                            <span class="menu-title">
                                                Shift Tetap
                                            </span>
                                        </a>
                                    </div>
                                    <div class="menu-item {{ request()->is('work-schedule/shift-rotating*') ? 'active' : '' }}">
                                        <a class="menu-link" href="{{ route('index') }}" tabindex="0">
                                            <span class="menu-title">
                                                Shift Bergilir
                                            </span>
                                        </a>
                                    </div>
                                </div>
                        </div>
                    @endcan
                </div>
            </div>
        </div>
    </div>
    <!-- End of Container -->
</div>
