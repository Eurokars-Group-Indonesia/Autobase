<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\DealerController;
use App\Http\Controllers\TransactionHeaderController;
use App\Http\Controllers\TransactionBodyController;
use App\Http\Controllers\SearchHistoryController;
use App\Http\Controllers\ImportHistoryController;
use App\Http\Controllers\PasswordResetController;

// Guest Routes
Route::middleware('guest')->group(function () {
    Route::get('/', [AuthController::class, 'showLogin'])->name('login');
    
    // Azure SSO Routes
    Route::get('/auth/azure', [AuthController::class, 'redirectToAzure'])->name('auth.azure');
    Route::get('/auth/azure/callback', [AuthController::class, 'handleAzureCallback'])->name('auth.azure.callback');
});

// Authenticated Routes
Route::middleware(['auth', 'has.role', 'throttle:web'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/chart-data', [DashboardController::class, 'getChartData'])->name('dashboard.chart-data');
    
    // User Management
    Route::middleware('permission:users.view')->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
    });
    Route::middleware('permission:users.create')->group(function () {
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
    });
    Route::middleware('permission:users.edit')->group(function () {
        Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::patch('/users/{user}', [UserController::class, 'update']);
    });
    Route::middleware('permission:users.delete')->group(function () {
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    });
    Route::middleware('permission:users.edit')->group(function () {
        Route::post('/users/sync-azure', [UserController::class, 'syncFromAzure'])->name('users.sync.azure');
    });
    
    // Role Management
    Route::middleware('permission:roles.view')->group(function () {
        Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
    });
    Route::middleware('permission:roles.create')->group(function () {
        Route::get('/roles/create', [RoleController::class, 'create'])->name('roles.create');
        Route::post('/roles', [RoleController::class, 'store'])->name('roles.store');
    });
    Route::middleware('permission:roles.edit')->group(function () {
        Route::get('/roles/{role}/edit', [RoleController::class, 'edit'])->name('roles.edit');
        Route::put('/roles/{role}', [RoleController::class, 'update'])->name('roles.update');
        Route::patch('/roles/{role}', [RoleController::class, 'update']);
    });
    Route::middleware('permission:roles.delete')->group(function () {
        Route::delete('/roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy');
    });
    
    // Permission Management
    Route::middleware('permission:permissions.view')->group(function () {
        Route::get('/permissions', [PermissionController::class, 'index'])->name('permissions.index');
    });
    Route::middleware('permission:permissions.create')->group(function () {
        Route::get('/permissions/create', [PermissionController::class, 'create'])->name('permissions.create');
        Route::post('/permissions', [PermissionController::class, 'store'])->name('permissions.store');
    });
    Route::middleware('permission:permissions.edit')->group(function () {
        Route::get('/permissions/{permission}/edit', [PermissionController::class, 'edit'])->name('permissions.edit');
        Route::put('/permissions/{permission}', [PermissionController::class, 'update'])->name('permissions.update');
        Route::patch('/permissions/{permission}', [PermissionController::class, 'update']);
    });
    Route::middleware('permission:permissions.delete')->group(function () {
        Route::delete('/permissions/{permission}', [PermissionController::class, 'destroy'])->name('permissions.destroy');
    });
    
    // Menu Management
    Route::middleware('permission:menus.view')->group(function () {
        Route::get('/menus', [MenuController::class, 'index'])->name('menus.index');
    });
    Route::middleware('permission:menus.create')->group(function () {
        Route::get('/menus/create', [MenuController::class, 'create'])->name('menus.create');
        Route::post('/menus', [MenuController::class, 'store'])->name('menus.store');
    });
    Route::middleware('permission:menus.edit')->group(function () {
        Route::get('/menus/{menu}/edit', [MenuController::class, 'edit'])->name('menus.edit');
        Route::put('/menus/{menu}', [MenuController::class, 'update'])->name('menus.update');
        Route::patch('/menus/{menu}', [MenuController::class, 'update']);
    });
    Route::middleware('permission:menus.delete')->group(function () {
        Route::delete('/menus/{menu}', [MenuController::class, 'destroy'])->name('menus.destroy');
    });
    
    // Brand Management
    Route::middleware('permission:brands.view')->group(function () {
        Route::get('/brands', [BrandController::class, 'index'])->name('brands.index');
    });
    Route::middleware('permission:brands.create')->group(function () {
        Route::get('/brands/create', [BrandController::class, 'create'])->name('brands.create');
        Route::post('/brands', [BrandController::class, 'store'])->name('brands.store');
    });
    Route::middleware('permission:brands.edit')->group(function () {
        Route::get('/brands/{brand}/edit', [BrandController::class, 'edit'])->name('brands.edit');
        Route::put('/brands/{brand}', [BrandController::class, 'update'])->name('brands.update');
        Route::patch('/brands/{brand}', [BrandController::class, 'update']);
    });
    Route::middleware('permission:brands.delete')->group(function () {
        Route::delete('/brands/{brand}', [BrandController::class, 'destroy'])->name('brands.destroy');
    });
    
    // Dealer Management
    Route::middleware('permission:dealers.view')->group(function () {
        Route::get('/dealers', [DealerController::class, 'index'])->name('dealers.index');
    });
    Route::middleware('permission:dealers.create')->group(function () {
        Route::get('/dealers/create', [DealerController::class, 'create'])->name('dealers.create');
        Route::post('/dealers', [DealerController::class, 'store'])->name('dealers.store');
    });
    Route::middleware('permission:dealers.edit')->group(function () {
        Route::get('/dealers/{dealer}/edit', [DealerController::class, 'edit'])->name('dealers.edit');
        Route::put('/dealers/{dealer}', [DealerController::class, 'update'])->name('dealers.update');
        Route::patch('/dealers/{dealer}', [DealerController::class, 'update']);
    });
    Route::middleware('permission:dealers.delete')->group(function () {
        Route::delete('/dealers/{dealer}', [DealerController::class, 'destroy'])->name('dealers.destroy');
    });
    
    // Transaction Headers
    Route::middleware('permission:transactions.view')->group(function () {
        Route::get('/transactions', [TransactionHeaderController::class, 'index'])->name('transactions.index');
        Route::get('/transactions/search', [TransactionHeaderController::class, 'search'])->name('transactions.search');
        Route::get('/transactions/body-details', [TransactionHeaderController::class, 'getBodyDetails'])->name('transactions.body.details');
        Route::get('/transactions/export', [TransactionHeaderController::class, 'export'])->name('transactions.export');
    });
    Route::middleware('permission:transactions.header.import')->group(function () {
        Route::get('/transactions/import', [TransactionHeaderController::class, 'showImport'])->name('transactions.header.import');
        Route::post('/transactions/import', [TransactionHeaderController::class, 'import'])->name('transactions.header.import.process');
        Route::get('/transactions/import/template', [TransactionHeaderController::class, 'downloadTemplate'])->name('transactions.header.import.template');
    });
    
    // Transaction Body
    Route::middleware('permission:transaction-body.view')->group(function () {
        Route::get('/transaction-body', [TransactionBodyController::class, 'index'])->name('transaction-body.index');
        Route::get('/transaction-body/search', [TransactionBodyController::class, 'search'])->name('transaction-body.search');
        Route::get('/transaction-body/export', [TransactionBodyController::class, 'export'])->name('transaction-body.export');
    });
    Route::middleware('permission:transaction-body.import')->group(function () {
        Route::get('/transaction-body/import', [TransactionBodyController::class, 'showImport'])->name('transaction-body.import');
        Route::post('/transaction-body/import', [TransactionBodyController::class, 'import'])->name('transaction-body.import.process');
        Route::get('/transaction-body/import/template', [TransactionBodyController::class, 'downloadTemplate'])->name('transaction-body.import.template');
    });
    
    // Search History
    Route::middleware('permission:search-history.view')->group(function () {
        Route::get('/search-history', [SearchHistoryController::class, 'index'])->name('search-history.index');
    });
    
    // Import History
    Route::middleware('permission:import-history.view')->group(function () {
        Route::get('/import-history', [ImportHistoryController::class, 'index'])->name('import-history.index');
    });
    
    // Test Email (Admin only)
    Route::middleware('role:ADMIN')->group(function () {
        Route::get('/test-email', [\App\Http\Controllers\TestEmailController::class, 'index'])->name('test-email.index');
        Route::post('/test-email/send', [\App\Http\Controllers\TestEmailController::class, 'send'])->name('test-email.send');
    });
});
