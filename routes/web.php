<?php

use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PlanSelectionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\User\CustomerController;
use App\Http\Controllers\User\PdfSettingsController;
use App\Http\Controllers\User\UserCompanyController;
use App\Http\Controllers\User\UserCredentialsController;
use App\Http\Controllers\User\UserDashboardController;
use App\Http\Controllers\User\UserInvoiceController;
use App\Http\Controllers\User\UserSettingsController;
use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Route;

// Bulk upload template route without auth for testing
Route::get('/app/invoices/bulk-upload-template', [UserInvoiceController::class, 'bulkUploadTemplate'])->name('user.invoices.bulk-upload-template');

Route::get('/', function () {
    return view('welcome');
});

Route::get('/test', function () {
    return response('Test route working!');
});

Route::post('/select-plan', [PlanSelectionController::class, 'select'])->name('plan.select');

// Redirect old dashboard route to new user app
Route::get('/dashboard', function () {
    return redirect('/app');
})->middleware(['auth', 'verified', 'subscription.paid'])->name('dashboard');

// Payment routes (before subscription is active)
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/payment', [PaymentController::class, 'index'])->name('payment.index');
    Route::post('/payment', [PaymentController::class, 'store'])->name('payment.store');
    Route::get('/payment/status', [PaymentController::class, 'status'])->name('payment.status');
});

// User App Routes (Custom Blade Dashboard)
Route::prefix('app')->middleware(['auth', 'verified', 'subscription.paid'])->name('user.')->group(function () {
    Route::get('/', [UserDashboardController::class, 'index'])->name('dashboard');

    // API Key management
    Route::post('/api-key/generate', [UserDashboardController::class, 'generateApiKey'])->name('api-key.generate');
    Route::get('/api-key/info', [UserDashboardController::class, 'getApiKeyInfo'])->name('api-key.info');

    // Company management
    Route::get('/company', [UserCompanyController::class, 'show'])->name('company.show');
    Route::get('/company/edit', [UserCompanyController::class, 'edit'])->name('company.edit');
    Route::put('/company', [UserCompanyController::class, 'update'])->name('company.update');

    // LHDN Credentials (requires company)
    Route::resource('credentials', UserCredentialsController::class)->middleware('company.required');
    Route::post('/credentials/test', [UserCredentialsController::class, 'test'])->name('credentials.test')->middleware('company.required');

    // Customer management (requires company and TIN validation)
    Route::resource('customers', CustomerController::class)->middleware(['company.required', 'tin.verified']);
    Route::post('/customers/{customer}/validate-tin', [CustomerController::class, 'validateTin'])->name('customers.validate-tin')->middleware(['company.required', 'tin.verified']);
    Route::get('/api/customers/search', [CustomerController::class, 'search'])->name('customers.search')->middleware(['company.required', 'tin.verified']);
    Route::post('/customers/bulk-action', [CustomerController::class, 'bulkAction'])->name('customers.bulk-action')->middleware(['company.required', 'tin.verified']);
    Route::get('/customers/export', [CustomerController::class, 'export'])->name('customers.export')->middleware(['company.required', 'tin.verified']);

    // Invoice management (requires company and TIN validation)
    Route::get('/invoices/submission', [UserInvoiceController::class, 'submission'])->name('invoices.submission')->middleware(['company.required', 'tin.verified']);
    Route::get('/invoices/cancellation', [UserInvoiceController::class, 'cancellation'])->name('invoices.cancellation')->middleware(['company.required', 'tin.verified']);
    Route::post('/invoices/cancel-invoice', [UserInvoiceController::class, 'cancelInvoice'])->name('invoices.cancel-invoice')->middleware(['company.required', 'tin.verified']);
    Route::get('/invoices/rejection', [UserInvoiceController::class, 'rejection'])->name('invoices.rejection')->middleware(['company.required', 'tin.verified']);
    Route::resource('invoices', UserInvoiceController::class)->middleware(['company.required', 'tin.verified']);
    Route::post('/invoices/{invoice:uuid}/mark-as-paid', [UserInvoiceController::class, 'markAsPaid'])->name('invoices.mark-as-paid')->middleware(['company.required', 'tin.verified']);
    Route::post('/invoices/{invoice:uuid}/submit', [UserInvoiceController::class, 'submit'])->name('invoices.submit')->middleware(['company.required', 'tin.verified']);
    Route::post('/invoices/bulk-submit', [UserInvoiceController::class, 'bulkSubmit'])->name('invoices.bulk-submit')->middleware(['company.required', 'tin.verified']);
    Route::post('/invoices/{invoice:uuid}/check-status', [UserInvoiceController::class, 'checkStatus'])->name('invoices.check-status')->middleware(['company.required', 'tin.verified']);
    Route::get('/invoices/{invoice:uuid}/pdf', [UserInvoiceController::class, 'pdf'])->name('invoices.pdf')->middleware(['company.required', 'tin.verified']);
    Route::get('/invoices/{invoice:uuid}/preview', [UserInvoiceController::class, 'preview'])->name('invoices.preview')->middleware(['company.required', 'tin.verified']);

    Route::post('/invoices/bulk-upload-process', [UserInvoiceController::class, 'bulkUploadProcess'])->name('invoices.bulk-upload-process')->middleware(['company.required', 'tin.verified']);
    Route::post('/invoices/bulk-create-and-submit', [UserInvoiceController::class, 'bulkCreateAndSubmit'])->name('invoices.bulk-create-and-submit')->middleware(['company.required', 'tin.verified']);
    Route::get('/invoices/{id}/data', [UserInvoiceController::class, 'getInvoiceData'])->name('invoices.get-data');

    // Settings
    Route::get('/settings', [UserSettingsController::class, 'index'])->name('settings');
    Route::put('/settings/profile', [UserSettingsController::class, 'updateProfile'])->name('settings.update-profile');
    Route::put('/settings/company', [UserSettingsController::class, 'updateCompany'])->name('settings.update-company');

    // PDF Settings
    Route::get('/settings/pdf', [PdfSettingsController::class, 'index'])->name('settings.pdf')->middleware('company.required');
    Route::put('/settings/pdf', [PdfSettingsController::class, 'update'])->name('settings.pdf.update')->middleware('company.required');
    Route::post('/settings/pdf/preview', [PdfSettingsController::class, 'preview'])->name('settings.pdf.preview')->middleware('company.required');
    Route::post('/settings/pdf/logo', [PdfSettingsController::class, 'uploadLogo'])->name('settings.pdf.logo.upload')->middleware('company.required');
    Route::delete('/settings/pdf/logo', [PdfSettingsController::class, 'removeLogo'])->name('settings.pdf.logo.remove')->middleware('company.required');
    Route::post('/settings/pdf/reset', [PdfSettingsController::class, 'reset'])->name('settings.pdf.reset')->middleware('company.required');

    // Debug PDF route
    Route::get('/debug-pdf', function () {
        try {
            $invoice = Invoice::with(['items', 'company'])->first();
            if (! $invoice) {
                return response('No invoices found', 404);
            }

            // Test with simple HTML first
            $html = '<html><body><h1>Invoice: '.$invoice->invoice_number.'</h1><p>Company: '.$invoice->company->name.'</p></body></html>';

            $pdf = Pdf::loadHTML($html);
            $pdf->setPaper('A4', 'portrait');

            return $pdf->stream('test-invoice.pdf');

        } catch (Exception $e) {
            return response('Error: '.$e->getMessage().' in '.$e->getFile().':'.$e->getLine(), 500);
        }
    });
    Route::put('/settings/credentials', [UserSettingsController::class, 'updateCredentials'])->name('settings.update-credentials');
    Route::put('/settings/invoice', [UserSettingsController::class, 'updateInvoiceSettings'])->name('settings.update-invoice');
});

// Profile routes (keep existing)
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

// Simple PDF test route (outside auth for debugging)
Route::get('/test-pdf', function () {
    try {
        $html = '<html><body><h1>PDF Test</h1><p>This is a simple PDF test.</p></body></html>';

        $pdf = Pdf::loadHTML($html);
        $pdf->setPaper('A4', 'portrait');

        return $pdf->stream('test.pdf');

    } catch (Exception $e) {
        return response('Error: '.$e->getMessage(), 500);
    }
});
