<!-- resources/views/layouts/submenu.blade.php -->
@foreach ($submenu as $subItem)
    @if (\App\Helpers\MenuHelper::canAccessMenuItem($subItem))
        @if (isset($subItem['children']))
            <!-- Parent submenu item with accordion functionality -->
            <div class="menu-item menu-item-accordion {{ \App\Helpers\MenuHelper::isActive($subItem, $depth) }}"
                data-menu-item-toggle="accordion" data-menu-item-trigger="click">
                <div class="menu-link border border-transparent grow cursor-pointer gap-[14px] pl-[10px] pr-[10px] py-[8px]">
                    <span class="menu-bullet flex w-[6px] relative before:absolute before:top-0 before:size-[6px] before:rounded-full menu-item-active:before:bg-primary"></span>
                    <span class="menu-title text-2sm font-normal mr-1 text-gray-800 menu-item-active:text-primary menu-item-active:font-medium menu-link-hover:!text-primary">
                        {{ $subItem['title'] }}
                    </span>
                    <span class="menu-arrow text-gray-400 w-[20px] shrink-0 justify-end ml-1 mr-[-10px]">
                        <i class="ki-filled ki-plus text-2xs menu-item-show:hidden"></i>
                        <i class="ki-filled ki-minus text-2xs hidden menu-item-show:inline-flex"></i>
                    </span>
                </div>

                <!-- Recursive submenu inclusion for child items -->
                <div class="menu-accordion gap-0.5 relative before:absolute before:left-[32px] pl-[22px] before:top-0 before:bottom-0 before:border-l before:border-gray-200 {{ \App\Helpers\MenuHelper::isActive($subItem, $depth + 1) }}">
                    @include('layouts.submenu', ['submenu' => $subItem['children'], 'depth' => $depth + 1])
                </div>
            </div>
        @else
            <!-- Individual submenu item without children -->
            <div class="menu-item {{ \App\Helpers\MenuHelper::isActive($subItem, $depth) }}">
                <a class="menu-link border border-transparent items-center grow menu-item-active:bg-secondary-active dark:menu-item-active:bg-coal-300 dark:menu-item-active:border-gray-100 menu-item-active:rounded-lg hover:bg-secondary-active dark:hover:bg-coal-300 dark:hover:border-gray-100 hover:rounded-lg gap-[14px] pl-[10px] pr-[10px] py-[8px]"
                    href="{{ route($subItem['route']) }}" tabindex="0">
                    <span class="menu-bullet flex w-[6px] relative before:absolute before:top-0 before:size-[6px] before:rounded-full before:-translate-x-1/2 before:-translate-y-1/2 menu-item-active:before:bg-primary menu-item-hover:before:bg-primary"></span>
                    <span class="menu-title text-2sm font-normal text-gray-800 menu-item-active:text-primary menu-item-active:font-semibold menu-link-hover:!text-primary">
                        {{ $subItem['title'] }}
                    </span>
                </a>
            </div>
        @endif
    @endif
@endforeach
