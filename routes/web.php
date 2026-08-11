<?php

use App\Http\Controllers\DocumentController;
use App\Http\Controllers\ReportExportController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect('/admin'));

Route::prefix('documents')->name('documents.')->group(function () {
    Route::get('/pos-receipt/{salesOrder}', [DocumentController::class, 'posReceipt'])->name('pos.receipt');
    Route::get('/sales-invoice/{salesOrder}', [DocumentController::class, 'salesInvoice'])->name('sales-invoice');
    Route::get('/purchase-invoice/{purchaseOrder}', [DocumentController::class, 'purchaseInvoice'])->name('purchase-invoice');
    Route::get('/product-labels', [DocumentController::class, 'productLabels'])->name('product-labels');
});

Route::prefix('reports/export')->name('reports.export.')->group(function () {
    Route::get('/daily-sales', [ReportExportController::class, 'dailySales'])->name('daily-sales');
    Route::get('/top-products', [ReportExportController::class, 'topProducts'])->name('top-products');
    Route::get('/top-customers', [ReportExportController::class, 'topCustomers'])->name('top-customers');
    Route::get('/sales-by-staff', [ReportExportController::class, 'salesByStaff'])->name('sales-by-staff');
    Route::get('/margin', [ReportExportController::class, 'margin'])->name('margin');
    Route::get('/inventory-valuation', [ReportExportController::class, 'inventoryValuation'])->name('inventory-valuation');
    Route::get('/low-stock', [ReportExportController::class, 'lowStock'])->name('low-stock');
    Route::get('/spend-by-supplier', [ReportExportController::class, 'spendBySupplier'])->name('spend-by-supplier');
    Route::get('/outstanding-purchase-orders', [ReportExportController::class, 'outstandingPurchaseOrders'])->name('outstanding-purchase-orders');
});
