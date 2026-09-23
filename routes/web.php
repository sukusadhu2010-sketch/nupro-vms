<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\VendorController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ProductCategoryController;
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

// Quotation Form (Structured Quotation / Offer Document)
use App\Http\Controllers\Admin\QuotationFormController;
Route::middleware(['auth'])->group(function () {
    Route::get('quotation-forms', [QuotationFormController::class, 'index'])->name('quotation-forms.index');
    Route::get('quotation-forms/create', [QuotationFormController::class, 'create'])->name('quotation-forms.create');
    Route::post('quotation-forms', [QuotationFormController::class, 'store'])->name('quotation-forms.store');
    Route::get('quotation-forms/{quotationForm}/edit', [QuotationFormController::class, 'edit'])->name('quotation-forms.edit');
    Route::put('quotation-forms/{quotationForm}', [QuotationFormController::class, 'update'])->name('quotation-forms.update');
    Route::get('quotation-forms/{quotationForm}', [QuotationFormController::class, 'show'])->name('quotation-forms.show');
    Route::delete('quotation-forms/{quotationForm}', [QuotationFormController::class, 'destroy'])->name('quotation-forms.destroy');
    Route::get('quotation-forms/{quotationForm}/print', [QuotationFormController::class, 'print'])->name('quotation-forms.print');
    Route::post('quotation-forms/{quotationForm}/finalize', [QuotationFormController::class, 'finalize'])->name('quotation-forms.finalize');
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

// Financial Year & Organization Settings
use App\Http\Controllers\Admin\FinancialYearController;
use App\Http\Controllers\Admin\OrganizationSettingController;
Route::middleware(['auth'])->group(function () {
    Route::resource('financial-years', FinancialYearController::class)
        ->parameters(['financial-years' => 'financial_year'])->except(['show']);
    Route::post('financial-years/{financial_year}/activate', [FinancialYearController::class, 'activate'])->name('financial-years.activate');
    Route::get('organization', [OrganizationSettingController::class, 'edit'])->name('organization.edit');
    Route::put('organization', [OrganizationSettingController::class, 'update'])->name('organization.update');
});

// Admin Routes
Route::middleware(['auth'])->group(function () {
  //  Route::resource('admin.enquiries', \App\Http\Controllers\Admin\EnquiryController::class);
    Route::resource('users', UserController::class);
    // Legacy combined vendor management (kept for compatibility)
    Route::resource('vendors', VendorController::class);
    Route::post('vendors/{vendor}/toggle-status', [VendorController::class, 'toggleStatus'])->name('vendors.toggle-status');

    // Foundry Management
    Route::resource('foundry-vendors', VendorController::class)->parameters(['foundry-vendors' => 'vendor'])->names('foundry-vendors');
    Route::post('foundry-vendors/{vendor}/toggle-status', [VendorController::class, 'toggleStatus'])->name('foundry-vendors.toggle-status');

    // Sub Vendor Management
    Route::resource('sub-vendors', VendorController::class)->parameters(['sub-vendors' => 'vendor'])->names('sub-vendors');
    Route::post('sub-vendors/{vendor}/toggle-status', [VendorController::class, 'toggleStatus'])->name('sub-vendors.toggle-status');
    Route::resource('customers', CustomerController::class);
    Route::post('customers/{customer}/toggle-status', [CustomerController::class, 'toggleStatus'])->name('customers.toggle-status');
    Route::resource('products', ProductController::class);
    Route::post('products/{product}/toggle-status', [ProductController::class, 'toggleStatus'])->name('products.toggle-status');
    Route::resource('product-categories', ProductCategoryController::class);
    Route::post('product-categories/{product_category}/toggle-status', [ProductCategoryController::class, 'toggleStatus'])->name('product-categories.toggle-status');
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

