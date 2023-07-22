<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class SettingsController extends Controller
{
    public function index () 
    {
        $user = session('user_object');
        return Inertia::render('Settings', [
            "user" => $user,
            "company" => $user->company,
        ]); 
    }
}
