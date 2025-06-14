<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\ProductController;
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
});
Route::middleware(['auth', 'role:admin,cajero'])->group(function () {
    Route::resource('products', ProductController::class);
    Route::resource('inventory', InventoryMovementController::class)->only(['index', 'create', 'store']);
});
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::resource('washers', WasherController::class);
});
Route::middleware(['auth', 'role:admin,cajero'])->group(function () {
    Route::resource('tickets', TicketController::class)->except(['show']);
    Route::resource('petty-cash', PettyCashExpenseController::class)->except(['show', 'edit', 'update']);
});
require __DIR__.'/auth.php';
