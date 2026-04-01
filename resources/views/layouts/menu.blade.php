<!-- resources/views/layouts/menu.blade.php -->

@php
    $menu = \App\Helpers\MenuHelper::getMenu();
@endphp
@foreach ($menu as $menuItem)
    @if (isset($menuItem['header']))
        <div class="menu-item pt-2.25 pb-px">
            <span class="menu-heading uppercase text-2sm font-medium text-gray-500 pl-[10px] pr-[10px]">
                {{ $menuItem['header'] }}
            </span>
        </div>
    @else
        @if (isset($menuItem['children']))
            <!-- Parent item with accordion functionality -->
            <div class="menu-item menu-item-accordion {{ \App\Helpers\MenuHelper::isActive($menuItem, 1) }}"
                 data-menu-item-toggle="accordion" data-menu-item-trigger="click">
                <div class="menu-link flex items-center grow cursor-pointer border border-transparent gap-[10px] pl-[10px] pr-[10px] py-[6px]"
                    tabindex="0">
                    <span class="menu-icon items-start text-gray-500 dark:text-gray-400 w-[20px]">
                        <i class="{{ $menuItem['icon'] }} text-lg"></i>
                    </span>
                    <span class="menu-title text-sm font-semibold text-gray-700 menu-item-active:text-primary menu-link-hover:!text-primary">
                        {{ $menuItem['title'] }}
                    </span>
                    <span class="menu-arrow text-gray-400 w-[20px] shrink-0 justify-end ml-1 mr-[-10px]">
                        <i class="ki-filled ki-plus text-2xs menu-item-show:hidden"></i>
                        <i class="ki-filled ki-minus text-2xs hidden menu-item-show:inline-flex"></i>
                    </span>
                </div>
                <div class="menu-accordion gap-0.5 pl-[10px] relative before:absolute before:left-[20px] before:top-0 before:bottom-0 before:border-l before:border-gray-200 {{ \App\Helpers\MenuHelper::isActive($menuItem, 1) }}">
                    <!-- Recursive include for child items -->
                    @include('layouts.submenu', ['submenu' => $menuItem['children'], 'depth' => 2])
                </div>
            </div>
        @else
            <!-- Individual menu item without children -->
            <div class="menu-item {{ \App\Helpers\MenuHelper::isActive($menuItem, 1) }}">
                <a class="menu-link flex items-center grow cursor-pointer border border-transparent gap-[10px] pl-[10px] pr-[10px] py-[6px]"
                    href="{{ route($menuItem['route']) }}">
                    <span class="menu-icon items-start text-gray-500 dark:text-gray-400 w-[20px]">
                        <i class="{{ $menuItem['icon'] }} text-lg"></i>
                    </span>
                    <span class="menu-title text-sm font-semibold text-gray-700 menu-item-active:text-primary menu-link-hover:!text-primary">
                        {{ $menuItem['title'] }}
                    </span>
                </a>
            </div>
        @endif
    @endif
@endforeach
