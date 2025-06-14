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
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

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
});
Route::middleware(['auth', 'role:admin,cajero'])->group(function () {
    Route::resource('products', ProductController::class);
    Route::resource('drinks', DrinkController::class);
    Route::resource('inventory', InventoryMovementController::class)->only(['index', 'create', 'store']);
});
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::post('washers/pay-all', [WasherController::class, 'payAll'])->name('washers.payAll');
    Route::post('washers/{washer}/pay', [WasherController::class, 'pay'])->name('washers.pay');
    Route::resource('washers', WasherController::class);
});
Route::middleware(['auth', 'role:admin,cajero'])->group(function () {
    Route::get('tickets/canceled', [TicketController::class, 'canceled'])->name('tickets.canceled');
    Route::post('tickets/{ticket}/cancel', [TicketController::class, 'cancel'])->name('tickets.cancel');
    Route::resource('tickets', TicketController::class)->except(['show', 'edit', 'update']);
    Route::resource('petty-cash', PettyCashExpenseController::class)->except(['show', 'edit', 'update']);
});
require __DIR__.'/auth.php';
