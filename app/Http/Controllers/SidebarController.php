<?php

namespace App\Http\Controllers;

use App\Helpers\MenuHelper;

class SidebarController extends Controller
{
    public static function getMenuData(): array
    {
        return MenuHelper::getMenuGroups();
    }
}
