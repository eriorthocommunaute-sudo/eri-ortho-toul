<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Finance\ContributionController;
use App\Http\Controllers\Finance\DonationController;
use App\Http\Controllers\Finance\FinancialEntryController;
use App\Http\Controllers\Finance\FoundingCotisationController;
use App\Http\Controllers\Finance\OffrandeController;
use App\Http\Controllers\Finance\PaymentController;
use App\Http\Controllers\Finance\PriestController;
use App\Http\Controllers\Finance\RecurringExpenseController;
use App\Http\Controllers\Finance\ReceiptController;
use App\Http\Controllers\FinancialRegisterController;
use App\Http\Controllers\Events\EventController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\Logistics\EquipmentController;
use App\Http\Controllers\Logistics\PriestIndemnityController;
use App\Http\Controllers\Logistics\PriestTransportController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\PdfExportController;
use App\Http\Controllers\Purchases\ExceptionalDemandController;
use App\Http\Controllers\Purchases\PurchaseRequestController;
use App\Http\Controllers\Secretary\ContributionNoticeController;
use App\Http\Controllers\Secretary\MeetingMinuteController;
use Illuminate\Support\Facades\Route;

Route::post('/locale/{locale}', LocaleController::class)->name('locale.update');

Route::get('/', function () {
    app()->setLocale(session('locale', config('app.locale')));

    return view('home');
})->name('home');

Route::middleware(['auth', 'locale'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/members/print', [MemberController::class, 'print'])->name('members.print')->middleware('permission:members.view');
    Route::get('/members/{member}/financial-history', [MemberController::class, 'financialHistory'])->name('members.financial-history')->middleware('permission:members.financial-history.view');
    Route::get('/members/{member}/financial-history/export', [MemberController::class, 'exportFinancialHistory'])->name('members.financial-history.export')->middleware('permission:members.financial-history.view');
    Route::resource('members', MemberController::class)->middleware('permission:members.view');

    // Finance
    Route::middleware('permission:finance.view')->group(function () {
        Route::get('contributions/matrix', [ContributionController::class, 'matrix'])->name('contributions.matrix');
        Route::get('contributions/matrix/export/pdf', [ContributionController::class, 'exportPdf'])->name('contributions.matrix.export.pdf');
        Route::get('contributions/matrix/export/excel', [ContributionController::class, 'exportExcel'])->name('contributions.matrix.export.excel');
        Route::resource('contributions', ContributionController::class)->names('contributions')->parameters(['contributions' => 'contribution']);
        Route::get('founding-cotisations-register', [FoundingCotisationController::class, 'register'])->name('founding-cotisations.register');
        Route::resource('founding-cotisations', FoundingCotisationController::class)->names('founding-cotisations')->parameters(['founding-cotisations' => 'foundingCotisation']);
        Route::get('receipts/create', fn (\Illuminate\Http\Request $request) => redirect()->route('payments.create', $request->query()))->name('receipts.create');
        Route::get('receipts/blank/print', [ReceiptController::class, 'printBlank'])->name('receipts.blank.print');
        Route::resource('receipts', ReceiptController::class)->only(['index', 'show', 'edit', 'update']);
        Route::post('receipts/{receipt}/confirm', [ReceiptController::class, 'confirm'])->name('receipts.confirm')->middleware('permission:finance.record');
        Route::post('receipts/{receipt}/cancel', [ReceiptController::class, 'cancel'])->name('receipts.cancel')->middleware('permission:finance.record');
        Route::delete('receipts/{receipt}', [ReceiptController::class, 'destroy'])->name('receipts.destroy')->middleware('permission:finance.record');
        Route::get('receipts/{receipt}/print', [ReceiptController::class, 'print'])->name('receipts.print');

        // Membership contribution payments (single or multi-month / advance)
        Route::get('payments/print-form', [PaymentController::class, 'formPrint'])->name('payments.form-print');
        Route::resource('payments', PaymentController::class)->only(['index', 'create', 'store', 'show'])->names('payments');
        Route::post('payments/{payment}/cancel', [PaymentController::class, 'cancel'])->name('payments.cancel')->middleware('permission:finance.record');
        Route::resource('offrandes', OffrandeController::class);
        Route::resource('donations', DonationController::class);
        Route::get('financial-entries/report', [FinancialEntryController::class, 'report'])->name('financial-entries.report');
        Route::resource('financial-entries', FinancialEntryController::class)->names('financial-entries');

        // Financial Register
        Route::get('financial-register', [FinancialRegisterController::class, 'index'])->name('financial-register.index')->middleware('permission:finance.view');
        Route::post('financial-register/generate-monthly', [FinancialRegisterController::class, 'generateMonthly'])->name('financial-register.generate-monthly')->middleware('permission:finance.record');
        Route::post('financial-register/generate-quarterly', [FinancialRegisterController::class, 'generateQuarterly'])->name('financial-register.generate-quarterly')->middleware('permission:finance.record');
        Route::get('financial-registers', [FinancialRegisterController::class, 'listRegisters'])->name('financial-register.list')->middleware('permission:finance.view');
        Route::get('financial-registers/{reference}/preview', [FinancialRegisterController::class, 'preview'])->name('financial-register.preview')->middleware('permission:finance.view');
        Route::get('financial-registers/{reference}/download', [FinancialRegisterController::class, 'download'])->name('financial-register.download')->middleware('permission:finance.view');

        // Priest expenses (transports & indemnities)
        Route::resource('priests', PriestController::class)->only(['index', 'create', 'store', 'edit', 'update']);
        Route::delete('priests/{priest}', [PriestController::class, 'destroy'])->name('priests.destroy')->middleware('permission:finance.record');
        Route::resource('priest-transports', PriestTransportController::class)->names('priest-transports')->parameters(['priest-transports' => 'transport']);
        Route::resource('priest-indemnities', PriestIndemnityController::class)->names('priest-indemnities')->parameters(['priest-indemnities' => 'indemnity']);
    });

    // Recurring expenses
    Route::middleware('permission:finance.view')->group(function () {
        Route::get('recurring-expenses', [RecurringExpenseController::class, 'index'])->name('recurring-expenses.index');
    });
    Route::middleware('permission:finance.record')->group(function () {
        Route::put('recurring-expenses/templates/{template}', [RecurringExpenseController::class, 'updateTemplate'])->name('recurring-expenses.templates.update');
        Route::post('recurring-expenses/{occurrence}/pay', [RecurringExpenseController::class, 'pay'])->name('recurring-expenses.pay');
    });

    // Logistics
    Route::middleware('permission:logistics.view')->group(function () {
        Route::resource('equipment', EquipmentController::class);
    });

    // Events
    Route::middleware('permission:events.view')->group(function () {
        Route::resource('events', EventController::class);
    });

    // Purchases & Expenses
    Route::middleware('permission:purchases.view')->group(function () {
        Route::get('purchase-requests/print', [PurchaseRequestController::class, 'print'])->name('purchase-requests.print');
        Route::get('purchase-requests/form-print', [PurchaseRequestController::class, 'formPrint'])->name('purchase-requests.form-print');
        Route::resource('purchase-requests', PurchaseRequestController::class)->names('purchase-requests')->only(['index', 'create', 'store', 'show', 'edit', 'update']);
        Route::post('purchase-requests/{purchaseRequest}/approve', [PurchaseRequestController::class, 'approve'])->name('purchase-requests.approve')->middleware('permission:purchases.manage');
        Route::post('purchase-requests/{purchaseRequest}/reject', [PurchaseRequestController::class, 'reject'])->name('purchase-requests.reject')->middleware('permission:purchases.manage');
        Route::post('purchase-requests/{purchaseRequest}/ordered', [PurchaseRequestController::class, 'markOrdered'])->name('purchase-requests.ordered')->middleware('permission:purchases.manage');
        Route::post('purchase-requests/{purchaseRequest}/received', [PurchaseRequestController::class, 'markReceived'])->name('purchase-requests.received')->middleware('permission:purchases.manage');
        Route::delete('purchase-requests/{purchaseRequest}', [PurchaseRequestController::class, 'destroy'])->name('purchase-requests.destroy')->middleware('permission:purchases.manage');

        Route::resource('exceptional-demands', ExceptionalDemandController::class)->names('exceptional-demands')->parameters(['exceptional-demands' => 'demand'])->only(['index', 'create', 'store', 'show', 'edit', 'update']);
        Route::post('exceptional-demands/{demand}/submit', [ExceptionalDemandController::class, 'submitForReview'])->name('exceptional-demands.submit')->middleware('permission:purchases.manage');
        Route::post('exceptional-demands/{demand}/approve', [PurchaseRequestController::class, 'approve'])->name('exceptional-demands.approve')->middleware('permission:purchases.manage');
        Route::post('exceptional-demands/{demand}/reject', [PurchaseRequestController::class, 'reject'])->name('exceptional-demands.reject')->middleware('permission:purchases.manage');
        Route::post('exceptional-demands/{demand}/paid', [PurchaseRequestController::class, 'markPaid'])->name('exceptional-demands.paid')->middleware('permission:purchases.manage');
        Route::delete('exceptional-demands/{demand}', [PurchaseRequestController::class, 'destroy'])->name('exceptional-demands.destroy')->middleware('permission:purchases.manage');
    });

    // Secretary
    Route::middleware('permission:secretary.view')->group(function () {
        Route::resource('contribution-notices', ContributionNoticeController::class)->names('contribution-notices')->parameters(['contribution-notices' => 'notice'])->only(['index', 'show']);
        Route::get('contributions/{contribution}/notices/generate', [ContributionNoticeController::class, 'generate'])->name('contribution-notices.generate')->middleware('permission:secretary.manage');
        Route::post('contribution-notices/{notice}/send', [ContributionNoticeController::class, 'send'])->name('contribution-notices.send')->middleware('permission:secretary.manage');
        Route::resource('meeting-minutes', MeetingMinuteController::class)->names('meeting-minutes')->parameters(['meeting-minutes' => 'minute'])->only(['index', 'create', 'store', 'show', 'edit', 'update']);
        Route::post('meeting-minutes/{minute}/approve', [MeetingMinuteController::class, 'approve'])->name('meeting-minutes.approve')->middleware('permission:secretary.manage');
        Route::delete('meeting-minutes/{minute}', [MeetingMinuteController::class, 'destroy'])->name('meeting-minutes.destroy')->middleware('permission:secretary.manage');
    });

    // PDF Export
    Route::middleware(['auth', 'locale', 'permission:finance.view'])->group(function () {
        Route::get('/pdf', [PdfExportController::class, 'index'])->name('pdf.index');
        Route::get('/pdf/{template}/{id?}', [PdfExportController::class, 'printView'])->name('pdf.print')->where('template', '.*');
    });
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');
});
Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->middleware('auth')->name('logout');
