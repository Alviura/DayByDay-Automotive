<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VehicleMakeController;
use App\Http\Controllers\VehicleModelController;
use App\Http\Controllers\WarehouseController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return redirect()->route(auth()->check() ? 'dashboard' : 'login');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Access control administration (M2). Per-action permissions are
    // enforced in the controllers' constructors.
    Route::resource('users', UserController::class);
    Route::resource('roles', RoleController::class)->except('show');

    // Master data (Phase 2). Per-action permissions enforced in controllers.
    Route::resource('warehouses', WarehouseController::class);
    Route::resource('shops', ShopController::class);
    Route::resource('suppliers', SupplierController::class);
    Route::resource('vehicle-makes', VehicleMakeController::class)->except(['show']);
    Route::resource('vehicle-models', VehicleModelController::class)->except(['show']);
});

require __DIR__.'/auth.php';
