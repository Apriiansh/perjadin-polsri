<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class PlaceholderController extends BaseController
{
    public function index(string $module): string
    {
        return view('dashboard/placeholder', [
            'title' => 'Modul ' . ucfirst($module),
            'module' => $module,
        ]);
    }
}
