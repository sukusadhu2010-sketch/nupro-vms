<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\VendorController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\EnquiryController;
use App\Http\Controllers\Admin\QuotationController;
use App\Http\Controllers\Admin\SalesOrderController;
use App\Http\Controllers\Admin\UnitController;

Route::get('/', function () {
    return view('welcome');
})->middleware('guest');

// Auth routes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// Protected dashboard routes
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'dashboard'])->name('dashboard');
});

// Enquiry Routes (Admin accessible)
Route::middleware(['auth'])->group(function () {
    Route::resource('enquiries', EnquiryController::class);
    Route::post('enquiries/{enquiry}/update-status', [EnquiryController::class, 'updateStatus'])->name('enquiries.update-status');
});

// Quotation Routes
Route::middleware(['auth'])->group(function () {
    Route::get('quotations', [QuotationController::class, 'index'])->name('quotations.index');
    Route::get('quotations/{quotation}', [QuotationController::class, 'show'])->name('quotations.show');
    Route::get('enquiries/{enquiry}/quotations/create', [QuotationController::class, 'create'])->name('quotations.create');
    Route::post('enquiries/{enquiry}/quotations', [QuotationController::class, 'store'])->name('quotations.store');
    Route::get('quotations/{quotation}/edit', [QuotationController::class, 'edit'])->name('quotations.edit');
    Route::put('quotations/{quotation}', [QuotationController::class, 'update'])->name('quotations.update');
    Route::delete('quotations/{quotation}', [QuotationController::class, 'destroy'])->name('quotations.destroy');
    Route::post('quotations/{quotation}/send', [QuotationController::class, 'send'])->name('quotations.send');
    Route::post('quotations/{quotation}/accept', [QuotationController::class, 'accept'])->name('quotations.accept');
    Route::get('quotations/{quotation}/revise', [QuotationController::class, 'revise'])->name('quotations.revise');
    Route::get('quotations/{quotation}/print', [QuotationController::class, 'print'])->name('quotations.print');
});

// Admin Routes
Route::middleware(['auth'])->group(function () {
  //  Route::resource('admin.enquiries', \App\Http\Controllers\Admin\EnquiryController::class);
    Route::resource('users', UserController::class);
    Route::resource('vendors', VendorController::class);
    Route::post('vendors/{vendor}/toggle-status', [VendorController::class, 'toggleStatus'])->name('vendors.toggle-status');
    Route::resource('customers', CustomerController::class);
    Route::post('customers/{customer}/toggle-status', [CustomerController::class, 'toggleStatus'])->name('customers.toggle-status');
    Route::resource('products', ProductController::class);
    Route::post('products/{product}/toggle-status', [ProductController::class, 'toggleStatus'])->name('products.toggle-status');
    Route::resource('units', UnitController::class);
    Route::post('units/{unit}/toggle-status', [UnitController::class, 'toggleStatus'])->name('units.toggle-status');
});

// Sales Order Routes
Route::middleware(['auth'])->group(function () {
    Route::resource('sales-orders', SalesOrderController::class);
    Route::post('sales-orders/{salesOrder}/procure', [\App\Http\Controllers\Admin\ProcurementController::class, 'generate'])->name('sales-orders.procure');
});

// Procurement Routes
Route::middleware(['auth'])->group(function () {
    Route::resource('procurements', \App\Http\Controllers\Admin\ProcurementController::class);
    Route::post('procurements/{procurement}/update-status', [\App\Http\Controllers\Admin\ProcurementController::class, 'updateStatus'])->name('procurements.update-status');
});

