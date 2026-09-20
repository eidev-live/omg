<?php

use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\SaleTypeController;
use App\Http\Controllers\StockController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::post('leads', [LeadController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('leads.store');

Route::middleware('auth')->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('leads', [LeadController::class, 'index'])->name('leads.index');
    Route::get('leads/export', [LeadController::class, 'export'])
        ->middleware('throttle:30,1')
        ->name('leads.export');

    Route::patch('customers/{customer}/toggle', [CustomerController::class, 'toggle'])->name('customers.toggle');
    Route::resource('customers', CustomerController::class)->except(['show']);

    Route::patch('sale-types/{sale_type}/toggle', [SaleTypeController::class, 'toggle'])->name('sale-types.toggle');
    Route::resource('sale-types', SaleTypeController::class)->except(['show']);

    Route::get('purchases', [PurchaseController::class, 'index'])->name('purchases.index');
    Route::get('purchases/create', [PurchaseController::class, 'create'])->name('purchases.create');
    Route::post('purchases', [PurchaseController::class, 'store'])->name('purchases.store');
    Route::get('purchases/{purchase}', [PurchaseController::class, 'show'])->name('purchases.show');
    Route::patch('purchases/{purchase}/cancel', [PurchaseController::class, 'cancel'])->name('purchases.cancel');

    Route::get('stock', [StockController::class, 'index'])->name('stock.index');
    Route::post('stock/adjustments', [StockController::class, 'adjust'])->name('stock.adjust');

    Route::get('sales', [SaleController::class, 'index'])->name('sales.index');
    Route::get('sales/create', [SaleController::class, 'create'])->name('sales.create');
    Route::post('sales', [SaleController::class, 'store'])->name('sales.store');
    Route::get('sales/{sale}', [SaleController::class, 'show'])->name('sales.show');
    Route::patch('sales/{sale}/payment', [SaleController::class, 'updatePayment'])->name('sales.payment');
    Route::patch('sales/{sale}/delivery', [SaleController::class, 'updateDelivery'])->name('sales.delivery');
    Route::patch('sales/{sale}/cancel', [SaleController::class, 'cancel'])->name('sales.cancel');

    Route::get('reports/sales', [ReportController::class, 'sales'])->name('reports.sales');
    Route::get('reports/purchases', [ReportController::class, 'purchases'])->name('reports.purchases');
    Route::get('reports/profit', [ReportController::class, 'profit'])->name('reports.profit');
    Route::get('reports/stock', [ReportController::class, 'stock'])->name('reports.stock');

    Route::middleware('throttle:30,1')->group(function () {
        Route::get('reports/sales/export', [ReportController::class, 'salesExport'])->name('reports.sales.export');
        Route::get('reports/purchases/export', [ReportController::class, 'purchasesExport'])->name('reports.purchases.export');
        Route::get('reports/profit/export', [ReportController::class, 'profitExport'])->name('reports.profit.export');
        Route::get('reports/stock/export', [ReportController::class, 'stockExport'])->name('reports.stock.export');
    });
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
