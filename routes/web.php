<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\TransactionsController;
use App\Http\Controllers\TopnavController;
use App\Http\Controllers\UserController;
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
        ->name('products.get')
        ->where('searchString', '.*');
    Route::get('/product/get/{productId}', [ProductController::class, 'getProduct'])
        ->name('product.get');
    Route::delete('/product/delete/{productId}', [ProductController::class, 'delete'])
        ->name('product.delete');
    Route::post('/product/create', [ProductController::class, 'create'])
        ->name('product.create');

    Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
    
    Route::get('/transactions', [TransactionsController::class, 'index'])
        ->name('transactions');
    Route::get('/transaction/get/{transactionId}/{userId?}', [TransactionsController::class, 'getTransaction'])
        ->name('transaction.get');
    Route::get('/transaction/search/{searchString}/{companyId}/{branchId}/{userId?}', [TransactionsController::class, 'searchTransaction'])
        ->name('transaction.search');
    Route::get('/transactions/get/{companyId}/{branchId}/{searchString?}/{transFrom?}/{transTo?}/{userId?}/{searchType?}', 
        [TransactionsController::class, 'getTransactions'])->name('transactions.get');
    Route::post('/transaction/save', [TransactionsController::class, 'createTransaction'])
        ->name('transaction.save');
    Route::get('/transaction/get/cash/currentbalance/{date}/{userId?}', [TransactionsController::class, 'getCurrentBalance'])
        ->name('transaction.get.currentbalance');
    Route::get('/transaction/get/cash/startingbalance/{date}/{userId?}', [TransactionsController::class, 'getStartingBalance'])
        ->name('transaction.get.startingbalance');
    Route::get('/transaction/get/total/sales/{date}/{userId?}', [TransactionsController::class, 'getTotalSales'])
        ->name('transaction.get.totalsales');
    Route::get('/transaction/get/total/expenses/{date}/{userId?}', [TransactionsController::class, 'getTotalExpenses'])
        ->name('transaction.get.expenses');

    Route::get('/branches', [TopnavController::class, 'getBranches'])
        ->name('branch.get');
    Route::post('/branch/change/{branchId}', [TopnavController::class, 'changeBranch'])
        ->name('branch.change');

    Route::get('/customers/get/{companyId?}/{customerType?}/{searchString?}', [CustomerController::class, 'get'])
        ->name('customers.get');
    Route::post('/customers/save', [CustomerController::class, 'save'])
        ->name('customers.create');

    Route::get('/user', [SettingsController::class, 'index'])
        ->name('user');
    Route::post('/user/save', [UserController::class, 'save'])
        ->name('user.save');
    Route::get('/users/get/{search?}', [UserController::class, 'getUsers'])
        ->name('users.get');
    Route::get('/user/get/{userId}', [UserController::class, 'getUser'])
        ->name('user.get');
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
