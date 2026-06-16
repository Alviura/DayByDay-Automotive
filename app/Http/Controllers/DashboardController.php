<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        // KPI wiring is implemented in the Dashboard module (M20).
        return view('dashboard');
    }
}
