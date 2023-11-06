<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\TransactionsController;
use App\Http\Controllers\TopnavController;
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
    
    Route::get('/', [DashboardController::class, 'index']);
    
    Route::get('/dashboard', [DashboardController::class, 'index'])->name("dashboard"); 

    Route::get('/products', [ProductController::class, 'index'])
        ->name('products');
    Route::get('/products/get/{companyId}/{searchString?}', [ProductController::class, 'getProducts'])
        ->name('products.get');
    Route::get('/product/get/{productId}', [ProductController::class, 'getProduct'])
        ->name('product.get');
    Route::post('/product/create', [ProductController::class, 'create'])
        ->name('product.create');

    Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
    
    Route::get('/transactions', [TransactionsController::class, 'index'])
        ->name('transactions');
    Route::get('/transaction/get/{transactionId}', [TransactionsController::class, 'getTransaction'])
        ->name('transaction.get');
    Route::get('/transaction/search/{searchString}/{companyId}/{branchId}', [TransactionsController::class, 'searchTransaction'])
        ->name('transaction.search');
    Route::get('/transactions/get/{companyId}/{branchId}/{searchString?}/{transFrom}/{transTo}', 
        [TransactionsController::class, 'getTransactions'])->name('transactions.get');
    Route::post('/transaction/save', [TransactionsController::class, 'createTransaction'])
        ->name('transaction.save');

    Route::get('/branches', [TopnavController::class, 'getBranches'])
        ->name('branch.get');
    Route::post('/branch/change/{branchId}', [TopnavController::class, 'changeBranch'])
        ->name('branch.change');

    Route::get('/customers/get/{companyId?}/{customerType?}/{searchString?}', [CustomerController::class, 'get'])
        ->name('customers.get');
    Route::post('/customers/save', [CustomerController::class, 'save'])
        ->name('customers.create');
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
