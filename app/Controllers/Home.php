<?php

namespace App\Controllers;

use CodeIgniter\HTTP\RedirectResponse;

class Home extends BaseController
{
    public function index(): RedirectResponse
    {
        if (auth()->loggedIn()) {
            return redirect()->to('/dashboard');
        }

        return redirect()->route('login');
    }
}
