<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Client\DashboardController as ClientDashboardController;
use App\Http\Controllers\Client\LeadController as ClientLeadController;
use App\Http\Controllers\Client\FollowUpController as ClientFollowUpController;
use App\Http\Controllers\Client\StaffController;
use App\Http\Controllers\Client\SearchController as ClientSearchController;
use App\Http\Controllers\Client\InvoiceController;
use App\Http\Controllers\PublicLeadFormController;
use App\Http\Controllers\PublicInvoiceController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\EmailVerificationController;

Route::get('/', fn () => redirect()->route('login'));

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.submit');

    Route::get('/forgot-password', [ForgotPasswordController::class, 'show'])->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'send'])->name('password.email');
    Route::get('/reset-password/{token}', [ResetPasswordController::class, 'show'])->name('password.reset');
    Route::post('/reset-password', [ResetPasswordController::class, 'reset'])->name('password.update');
});

Route::middleware('auth')->group(function () {
    Route::get('/email/verify', [EmailVerificationController::class, 'notice'])->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
        ->middleware('signed')->name('verification.verify');
    Route::post('/email/verification-notification', [EmailVerificationController::class, 'resend'])
        ->middleware('throttle:6,1')->name('verification.send');
});

Route::middleware('throttle:20,1')->group(function () {
    Route::get('/lead-form/{client:client_code}', [PublicLeadFormController::class, 'show'])->name('public.lead-form');
    Route::post('/lead-form/{client:client_code}', [PublicLeadFormController::class, 'store'])->name('public.lead-form.submit');
    Route::get('/invoice/{invoice}', [PublicInvoiceController::class, 'show'])->name('public.invoice.show');
});

Route::post('/logout', [LoginController::class, 'logout'])
    ->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/notifications/{id}/read', [\App\Http\Controllers\NotificationController::class, 'read'])->name('notifications.read');
    Route::post('/notifications/read-all', [\App\Http\Controllers\NotificationController::class, 'readAll'])->name('notifications.read-all');
});

Route::middleware(['auth', 'role:Admin|Staff|Telecaller', 'client.active'])->prefix('client')->name('client.')->group(function () {
    Route::get('/dashboard', [ClientDashboardController::class, 'index'])->name('dashboard');
    Route::get('/search', [ClientSearchController::class, 'index'])->name('search');
    Route::get('/analytics', [\App\Http\Controllers\Client\AnalyticsController::class, 'index'])->name('analytics');

    Route::post('leads/bulk', [ClientLeadController::class, 'bulk'])->name('leads.bulk');
    Route::get('leads/export', [ClientLeadController::class, 'export'])->name('leads.export');
    Route::get('leads/trash', [ClientLeadController::class, 'trash'])->name('leads.trash');
    Route::post('leads/{id}/restore', [ClientLeadController::class, 'restore'])->name('leads.restore');
    Route::delete('leads/{id}/force-delete', [ClientLeadController::class, 'forceDelete'])->name('leads.force-delete');
    Route::get('leads/check-duplicate', [ClientLeadController::class, 'checkDuplicate'])->name('leads.check-duplicate');
    Route::get('leads/import', [\App\Http\Controllers\Client\LeadImportController::class, 'show'])->name('leads.import');
    Route::post('leads/import', [\App\Http\Controllers\Client\LeadImportController::class, 'store'])->name('leads.import.store');
    Route::resource('leads', ClientLeadController::class);
    Route::post('leads/{lead}/status', [ClientLeadController::class, 'status'])->name('leads.status');
    Route::post('leads/{lead}/note', [ClientLeadController::class, 'addNote'])->name('leads.note');
    Route::post('leads/{lead}/interaction', [ClientLeadController::class, 'logInteraction'])->name('leads.interaction');
    Route::post('leads/{lead}/merge', [ClientLeadController::class, 'merge'])->name('leads.merge');
    Route::post('leads/{lead}/email', [ClientLeadController::class, 'sendEmail'])->name('leads.email');
    Route::post('leads/{lead}/attachments', [ClientLeadController::class, 'storeAttachment'])->name('leads.attachments.store');
    Route::get('leads/{lead}/attachments/{attachment}', [ClientLeadController::class, 'downloadAttachment'])->name('leads.attachments.download');
    Route::delete('leads/{lead}/attachments/{attachment}', [ClientLeadController::class, 'destroyAttachment'])->name('leads.attachments.destroy');

    Route::get('follow-ups', [ClientFollowUpController::class, 'index'])->name('follow-ups.index');
    Route::get('follow-ups/calendar', [ClientFollowUpController::class, 'calendar'])->name('follow-ups.calendar');
    Route::post('follow-ups/{followUp}/complete', [ClientFollowUpController::class, 'complete'])->name('follow-ups.complete');
    Route::post('follow-ups/{followUp}/reschedule', [ClientFollowUpController::class, 'reschedule'])->name('follow-ups.reschedule');
    Route::post('leads/{lead}/follow-ups', [ClientFollowUpController::class, 'store'])->name('leads.follow-ups.store');
    Route::post('leads/{lead}/whatsapp', [ClientLeadController::class, 'sendWhatsApp'])->name('leads.whatsapp');

    Route::get('invoices', [InvoiceController::class, 'index'])->name('invoices.index');
    Route::get('invoices/create', [InvoiceController::class, 'create'])->name('invoices.create');
    Route::post('invoices', [InvoiceController::class, 'store'])->name('invoices.store');
    Route::get('invoices/{invoice}', [InvoiceController::class, 'show'])->name('invoices.show');
    Route::get('invoices/{invoice}/edit', [InvoiceController::class, 'edit'])->name('invoices.edit');
    Route::put('invoices/{invoice}', [InvoiceController::class, 'update'])->name('invoices.update');
    Route::delete('invoices/{invoice}', [InvoiceController::class, 'destroy'])->name('invoices.destroy');
    Route::post('invoices/{invoice}/mark-paid', [InvoiceController::class, 'markPaid'])->name('invoices.mark-paid');
    Route::get('invoices/{invoice}/download', [InvoiceController::class, 'download'])->name('invoices.download');

    Route::get('attendances', [\App\Http\Controllers\Client\AttendanceController::class, 'index'])->name('attendances.index');
    Route::get('attendances/{attendance}', [\App\Http\Controllers\Client\AttendanceController::class, 'show'])->name('attendances.show');
    Route::post('attendances/check-in', [\App\Http\Controllers\Client\AttendanceController::class, 'checkIn'])->name('attendances.check-in');
    Route::post('attendances/check-out', [\App\Http\Controllers\Client\AttendanceController::class, 'checkOut'])->name('attendances.check-out');

    // Only the Admin manages staff accounts
    Route::middleware('role:Admin')->group(function () {
        Route::resource('staff', StaffController::class)->except(['show'])->parameters(['staff' => 'staffMember']);
        Route::post('staff/{staffMember}/toggle-active', [StaffController::class, 'toggleActive'])->name('staff.toggle-active');

        Route::get('api-tokens', [\App\Http\Controllers\Client\ApiTokenController::class, 'index'])->name('api-tokens.index');
        Route::post('api-tokens', [\App\Http\Controllers\Client\ApiTokenController::class, 'store'])->name('api-tokens.store');
        Route::delete('api-tokens/{id}', [\App\Http\Controllers\Client\ApiTokenController::class, 'destroy'])->name('api-tokens.destroy');

        Route::get('lead-form', [\App\Http\Controllers\Client\LeadFormController::class, 'index'])->name('lead-form.index');

        Route::get('settings/company', [\App\Http\Controllers\Client\CompanyController::class, 'edit'])->name('company.edit');
        Route::put('settings/company', [\App\Http\Controllers\Client\CompanyController::class, 'update'])->name('company.update');

        Route::get('settings/custom-fields', [\App\Http\Controllers\Client\CustomFieldController::class, 'index'])->name('custom-fields.index');
        Route::post('settings/custom-fields', [\App\Http\Controllers\Client\CustomFieldController::class, 'store'])->name('custom-fields.store');
        Route::delete('settings/custom-fields/{customField}', [\App\Http\Controllers\Client\CustomFieldController::class, 'destroy'])->name('custom-fields.destroy');

        Route::get('audit-logs', [\App\Http\Controllers\Client\AuditLogController::class, 'index'])->name('audit-logs.index');

        Route::get('reports', [\App\Http\Controllers\Client\ReportController::class, 'index'])->name('reports.index');
        Route::get('reports/export', [\App\Http\Controllers\Client\ReportController::class, 'export'])->name('reports.export');
    });
});
