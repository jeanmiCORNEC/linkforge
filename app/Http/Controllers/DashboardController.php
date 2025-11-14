<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Link;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        

        return Inertia::render('Dashboard');
    }
}
