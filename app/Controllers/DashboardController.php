<?php

namespace App\Controllers;

use App\Models\EmployeeModel;

class DashboardController extends BaseController
{
    public function index(): string
    {
        $employeeModel = new EmployeeModel();

        return view('dashboard/index', [
            'title'          => 'Dashboard',
            'totalEmployees' => $employeeModel->countAll(),
        ]);
    }
}
