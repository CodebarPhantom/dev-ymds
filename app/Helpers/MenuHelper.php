<?php
// app/Helpers/MenuHelper.php

namespace App\Helpers;

use Illuminate\Support\Facades\Auth;

class MenuHelper
{
    public static function getMenu()
    {
        $menuConfig = config('menus.main_menu');

        // Filter the menu based on user permissions/roles recursively
        $filteredMenu = array_filter($menuConfig, function ($menuItem) {
            return self::canAccessMenuItem($menuItem);
        });

        return $filteredMenu;
    }

    public static function isActive($menuItem, $depth = 1)
    {
        $currentUrl = request()->path();

        // Check if the current URL matches any path in the `pathUrl` array for the current item
        if (isset($menuItem['pathUrl']) && is_array($menuItem['pathUrl'])) {
            foreach ($menuItem['pathUrl'] as $path) {
                if (fnmatch($path, $currentUrl)) {
                    return ($depth === 1) ? 'active show' : 'active show';
                }
            }
        }

        // Recursively check children for active state
        if (isset($menuItem['children'])) {
            foreach ($menuItem['children'] as $child) {
                $childClass = self::isActive($child, $depth + 1);
                if ($childClass === 'active' || $childClass === 'active show') {
                    return ($depth === 1) ? 'active show' : 'active ';
                }
            }
        }

        return '';
    }




    public static function canAccessMenuItem($menuItem)
    {
        // Check if it's a header (no permission check needed for headers)
        if (isset($menuItem['header'])) {
            return true;
        }

        // Check for access to children recursively
        if (isset($menuItem['children'])) {
            foreach ($menuItem['children'] as $child) {
                if (self::canAccessMenuItem($child)) {
                    return true;
                }
            }
        }

        // If no children, check the current menu item's permissions or roles
        return self::canAccess($menuItem);
    }

    public static function hasAccessibleChildren($menuItem)
    {
        if (isset($menuItem['children'])) {
            foreach ($menuItem['children'] as $child) {
                if (self::canAccessMenuItem($child)) {
                    return true;
                }
            }
        }

        return false;
    }

    private static function canAccess($menuItem)
    {
        $user = Auth::user();

        // Check if permission array is provided
        if (isset($menuItem['permission'])) {
            // If permission is an array, check if the user has any of the permissions
            if (is_array($menuItem['permission'])) {
                foreach ($menuItem['permission'] as $permission) {
                    if ($user->can($permission)) {
                        return true;
                    }
                }
                return false;
            }

            // If permission is a string, check the single permission
            return $user->can($menuItem['permission']);
        }

        // Check for roles if 'role' is specified
        if (isset($menuItem['role']) && !$user->hasRole($menuItem['role'])) {
            return false;
        }

        return true;
    }
}
