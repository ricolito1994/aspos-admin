<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index () {
        $user = session('user_object');
        return Inertia::render('Dashboard', [
            "user" => $user,
            "company" => $user?->company,
        ]);
    }
}
