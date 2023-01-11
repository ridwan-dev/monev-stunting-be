<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseController;
use Illuminate\Http\Request;
use App\Models\Pub\MenuLevel1;

class CmsController extends BaseController
{
    public function __construct()
    {
        $this->middleware(
            [
                'auth:api', 
                'scopes:edit,create,delete'
            ])->except(
                [
                    'index', 
                    'show', 
                    'sidebarMenu'
                ]
            );
    }

    public function sidebarMenu()
    {
        $menus = MenuLevel1::with('submenu', 'submenu.submenu')->get();
        return $this->returnJsonSuccess("Menu fetched successfully", $menus);
    }
}
