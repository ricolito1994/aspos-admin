<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::middleware('auth')->group(function () {
    $user = session('user_object');
    
    Route::get('/', function () {
        $user = session('user_object');
        return Inertia::render('Dashboard', [
            /*'canLogin' => Route::has('login'),
            'canRegister' => Route::has('register'),
            'laravelVersion' => Application::VERSION,
            'phpVersion' => PHP_VERSION,*/
            "user" => $user,
            "company" => $user->company,
        ]);
    });
    
    Route::get('/dashboard', function () use (&$user) {  
        dd($user);
        return Inertia::render('Dashboard', [
            "user" => $user,
            "company" => $user->company,
        ]); 
    })->name("dashboard"); 

    Route::get('/products', function () { })->name('products');

    Route::get('/branches', function () { })->name('branches');

});





//->middleware(['auth'])

/* Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard'); */

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
