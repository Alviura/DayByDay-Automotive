<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:dashboard.view');
    }

    public function index(DashboardService $dashboard): View
    {
        return view('dashboard.index', $dashboard->forUser(auth()->user()));
    }
}
