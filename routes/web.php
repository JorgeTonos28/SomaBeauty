<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\DrinkController;
use App\Http\Controllers\WasherController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\PettyCashExpenseController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\InventoryMovementController;
use App\Http\Controllers\AppearanceController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('login');
});

use App\Http\Controllers\DashboardController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');
    Route::get('/dashboard/download', [DashboardController::class, 'download'])
        ->name('dashboard.download');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::resource('services', ServiceController::class);
    Route::resource('users', UserController::class)->except(['show']);
    Route::put('discounts/{discount}/activate', [\App\Http\Controllers\DiscountController::class, 'activate'])->name('discounts.activate');
    Route::put('discounts/{discount}/deactivate', [\App\Http\Controllers\DiscountController::class, 'deactivate'])->name('discounts.deactivate');
    Route::resource('discounts', \App\Http\Controllers\DiscountController::class);
    Route::resource('bank-accounts', \App\Http\Controllers\BankAccountController::class);
    Route::get('appearance', [AppearanceController::class, 'index'])->name('appearance.index');
    Route::post('appearance', [AppearanceController::class, 'store'])->name('appearance.store');
    Route::post('petty-cash/fund', [PettyCashExpenseController::class, 'updateFund'])->name('petty-cash.update-fund');
});
Route::middleware(['auth', 'role:admin,cajero'])->group(function () {
    Route::resource('products', ProductController::class);
    Route::resource('drinks', DrinkController::class);
    Route::get('inventory/out/create', [InventoryMovementController::class, 'createExit'])->name('inventory.createExit');
    Route::post('inventory/out', [InventoryMovementController::class, 'storeExit'])->name('inventory.storeExit');
    Route::resource('inventory', InventoryMovementController::class)->only(['index', 'create', 'store']);
});
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::post('washers/pay-all', [WasherController::class, 'payAll'])->name('washers.payAll');
    Route::post('washers/{washer}/pay', [WasherController::class, 'pay'])->name('washers.pay');
    Route::post('washers/commission-rate', [WasherController::class, 'updateCommissionRate'])->name('washers.updateCommissionRate');
    Route::resource('washers', WasherController::class);
});
Route::middleware(['auth', 'role:admin,cajero'])->group(function () {
    Route::get('vehicles/search', [\App\Http\Controllers\VehicleController::class, 'search'])->name('vehicles.search');
    Route::get('tickets/canceled', [TicketController::class, 'canceled'])->name('tickets.canceled');
    Route::get('tickets/pending', [TicketController::class, 'pending'])->name('tickets.pending');
    Route::post('tickets/{ticket}/pay', [TicketController::class, 'pay'])->name('tickets.pay');
    Route::post('tickets/{ticket}/cancel', [TicketController::class, 'cancel'])->name('tickets.cancel');
    Route::resource('tickets', TicketController::class);
    Route::resource('petty-cash', PettyCashExpenseController::class)->except(['show', 'edit', 'update']);
});
require __DIR__.'/auth.php';
