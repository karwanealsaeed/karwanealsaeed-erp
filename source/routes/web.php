<?php
use App\Http\Controllers\PublicFaqController;
use App\Http\Controllers\PublicGalleryController;
use App\Http\Controllers\PublicPakistanTourController;
use App\Http\Controllers\PublicTestimonialController;
use App\Http\Controllers\SupplierStatementPdfController;
use App\Http\Controllers\CustomerStatementPdfController;
use App\Http\Controllers\KarwanAiController;
use App\Http\Controllers\CustomerDocumentDownloadController;
use App\Http\Controllers\CustomerPortalController;
use App\Http\Controllers\DatabaseBackupDownloadController;
use App\Http\Controllers\GoogleDriveOAuthController;
use App\Http\Controllers\InvoicePrintController;
use App\Http\Controllers\PaymentReceiptController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicUmrahPackageController;
use App\Http\Controllers\SeoController;
use App\Http\Controllers\RefundPrintController;
use App\Http\Controllers\ReportExportController;
use App\Http\Controllers\StaffPayrollPrintController;
use App\Http\Controllers\SupportInquiryController;
use App\Http\Controllers\UmrahPackagePosterPrintController;
use App\Http\Controllers\UmrahVoucherPrintController;
use App\Http\Controllers\WebsiteController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Website
|--------------------------------------------------------------------------
*/

Route::get('/', [WebsiteController::class, 'home'])->name('website.home');
Route::get('/sitemap.xml', [SeoController::class, 'sitemap'])->name('website.sitemap');
Route::get('/language/{locale}', function (\Illuminate\Http\Request $request, string $locale) {
    abort_unless(in_array($locale, ['en', 'ur'], true), 404);

    session(['locale' => $locale]);

    $previousUrl = url()->previous();
    $previousHost = parse_url($previousUrl, PHP_URL_HOST);

    if ($previousHost !== $request->getHost()) {
        return redirect()->route('website.home');
    }

    return redirect()->to($previousUrl);
})->name('website.language');


Route::get('/about-us', [WebsiteController::class, 'page'])->defaults('slug', 'about-us')->name('website.about');
Route::get('/our-services', [WebsiteController::class, 'page'])->defaults('slug', 'our-services')->name('website.services');
Route::get('/umrah-packages', [PublicUmrahPackageController::class, 'index'])->name('website.umrah');
Route::get('/umrah-packages/{umrahPackage}', [PublicUmrahPackageController::class, 'show'])->name('website.umrah.show');
Route::get('/air-ticketing', [WebsiteController::class, 'page'])->defaults('slug', 'air-ticketing')->name('website.tickets');
Route::get('/hotel-booking', [WebsiteController::class, 'page'])->defaults('slug', 'hotel-booking')->name('website.hotels');
Route::get('/visit-visas', [WebsiteController::class, 'page'])->defaults('slug', 'visit-visas')->name('website.visas');
Route::get('/visa-consultancy', [WebsiteController::class, 'page'])->defaults('slug', 'visa-consultancy')->name('website.consultancy');
Route::get('/travel-insurance', [WebsiteController::class, 'page'])->defaults('slug', 'travel-insurance')->name('website.insurance');
Route::get(
    '/pakistan-tours',
    [PublicPakistanTourController::class, 'index'],
)->name('website.tours');

Route::get(
    '/pakistan-tours/{pakistanTour}',
    [PublicPakistanTourController::class, 'show'],
)->name('website.tours.show');

Route::get(
    '/gallery',
    [PublicGalleryController::class, 'index'],
)->name('website.gallery');

Route::get(
    '/gallery/{galleryItem}',
    [PublicGalleryController::class, 'show'],
)->name('website.gallery.show');
Route::get(
    '/testimonials',
    [PublicTestimonialController::class, 'index'],
)->name('website.testimonials');
Route::get(
    '/faq',
    [PublicFaqController::class, 'index'],
)->name('website.faq');
Route::get('/blog-news', [WebsiteController::class, 'page'])->defaults('slug', 'blog-news')->name('website.blog');
Route::get('/contact', [WebsiteController::class, 'page'])->defaults('slug', 'contact')->name('website.contact');

Route::post('/support-inquiries', [SupportInquiryController::class, 'store'])
    ->name('support-inquiries.store');
Route::post('/karwan-ai/chat', [KarwanAiController::class, 'chat'])
    ->name('karwan-ai.chat');

Route::get('/karwan-ai/history', [KarwanAiController::class, 'history'])
    ->name('karwan-ai.history');

Route::delete('/karwan-ai/clear', [KarwanAiController::class, 'clear'])
    ->name('karwan-ai.clear');
/*
|--------------------------------------------------------------------------
| Customer Portal Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/customer/login', [CustomerPortalController::class, 'login'])
    ->name('customer.portal.login');

Route::post('/customer/login', [CustomerPortalController::class, 'authenticate'])
    ->name('customer.portal.authenticate');

Route::get('/customer/register', [CustomerPortalController::class, 'register'])
    ->name('customer.portal.register');

Route::post('/customer/register', [CustomerPortalController::class, 'storeRegistration'])
    ->name('customer.portal.register.store');

/*
|--------------------------------------------------------------------------
| Customer Portal Protected Routes
|--------------------------------------------------------------------------
*/

Route::middleware('customer.portal')->group(function (): void {
    Route::get('/customer/dashboard', [CustomerPortalController::class, 'dashboard'])
        ->name('customer.portal.dashboard');

    Route::get('/customer/profile', [CustomerPortalController::class, 'profile'])
        ->name('customer.portal.profile');

    Route::get('/customer/ledger', [CustomerPortalController::class, 'ledger'])
        ->name('customer.portal.ledger');

    Route::get('/customer/visa-applications', [CustomerPortalController::class, 'visaApplications'])
        ->name('customer.portal.visa');

    Route::get('/customer/flight-bookings', [CustomerPortalController::class, 'flightBookings'])
        ->name('customer.portal.flights');

    Route::get('/customer/umrah-bookings', [CustomerPortalController::class, 'umrahBookings'])
        ->name('customer.portal.umrah');

    Route::get('/customer/hotel-bookings', [CustomerPortalController::class, 'hotelBookings'])
        ->name('customer.portal.hotels');

    Route::get('/customer/invoices', [CustomerPortalController::class, 'invoices'])
        ->name('customer.portal.invoices');

    Route::get('/customer/documents', [CustomerPortalController::class, 'documents'])
        ->name('customer.portal.documents');

    Route::get('/customer/vouchers', [CustomerPortalController::class, 'vouchers'])
        ->name('customer.portal.vouchers');

    Route::get('/customer/booking-tracking', [CustomerPortalController::class, 'bookingTracking'])
        ->name('customer.portal.tracking');

    Route::get('/customer/payment-uploads', [CustomerPortalController::class, 'paymentUploads'])
        ->name('customer.portal.payment_uploads');

    Route::post('/customer/payment-uploads', [CustomerPortalController::class, 'storePaymentUpload'])
        ->name('customer.portal.payment_uploads.store');

    Route::get('/customer/family-members', [CustomerPortalController::class, 'familyMembers'])
        ->name('customer.portal.family');

    Route::post('/customer/family-members', [CustomerPortalController::class, 'storeFamilyMember'])
        ->name('customer.portal.family.store');

    Route::delete('/customer/family-members/{familyMember}', [CustomerPortalController::class, 'deleteFamilyMember'])
        ->name('customer.portal.family.delete');

    Route::get('/customer/notifications', [CustomerPortalController::class, 'notifications'])
        ->name('customer.portal.notifications');
Route::post('/customer/notifications/{customerNotification}/read', [CustomerPortalController::class, 'markNotificationRead'])
    ->name('customer.portal.notifications.read');

Route::post('/customer/notifications/mark-all-read', [CustomerPortalController::class, 'markAllNotificationsRead'])
    ->name('customer.portal.notifications.mark_all_read');

    Route::get('/customer/support-tickets', [CustomerPortalController::class, 'supportTickets'])
        ->name('customer.portal.support');

    Route::post('/customer/support-tickets', [CustomerPortalController::class, 'storeSupportTicket'])
        ->name('customer.portal.support.store');

    Route::get('/customer/support-tickets/{supportTicket}', [CustomerPortalController::class, 'showSupportTicket'])
        ->name('customer.portal.support.show');

    Route::post('/customer/support-tickets/{supportTicket}/reply', [CustomerPortalController::class, 'replySupportTicket'])
        ->name('customer.portal.support.reply');

    Route::post('/customer/logout', [CustomerPortalController::class, 'logout'])
        ->name('customer.portal.logout');
});

/*
|--------------------------------------------------------------------------
| Admin / Auth Routes
|--------------------------------------------------------------------------
*/

Route::get('/dashboard', function () {
    return redirect('/admin');
})->middleware('auth');

Route::get('/invoices/{invoice}/print', InvoicePrintController::class)
    ->name('invoices.print')
    ->middleware('auth');

Route::get('/umrah-vouchers/{umrahVoucher}/print', UmrahVoucherPrintController::class)
    ->name('umrah-vouchers.print')
    ->middleware('auth');

Route::get('/umrah-package-posters/print', UmrahPackagePosterPrintController::class)
    ->name('umrah-package-posters.print')
    ->middleware('auth');

Route::get('/staff-payrolls/{staffPayroll}/print', StaffPayrollPrintController::class)
    ->name('staff-payrolls.print')
    ->middleware('auth');

Route::get('/payment-receipts/{type}/{payment}/print', PaymentReceiptController::class)
    ->whereIn('type', ['visa', 'flight', 'umrah', 'hotel'])
    ->name('payment-receipts.print')
    ->middleware('auth');

Route::get('/refunds/{refund}/print', RefundPrintController::class)
    ->name('refunds.print')
    ->middleware('auth');

Route::get('/reports/{report}/{format}', ReportExportController::class)
    ->where('format', 'print|pdf|excel')
    ->name('reports.export')
    ->middleware('auth');

Route::get('/database-backups/{databaseBackup}/download', DatabaseBackupDownloadController::class)
    ->name('database-backups.download')
    ->middleware('auth');

Route::get('/customer-documents/{customerDocument}/download/{fileIndex?}', CustomerDocumentDownloadController::class)
    ->name('customer-documents.download')
    ->middleware('auth');

Route::get('/google-drive/oauth/redirect', [GoogleDriveOAuthController::class, 'redirect'])
    ->name('google-drive.oauth.redirect')
    ->middleware('auth');

Route::get('/google-drive/oauth/callback', [GoogleDriveOAuthController::class, 'callback'])
    ->name('google-drive.oauth.callback')
    ->middleware('auth');

Route::middleware('auth')->group(function (): void {
    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');
});

require __DIR__ . '/auth.php';
Route::get(
    '/shared/customer-statement/pdf',
    CustomerStatementPdfController::class
)
    ->middleware([
        'signed',
        'throttle:30,1',
    ])
    ->name('customer-statements.pdf');
Route::get(
    '/shared/supplier-statement/pdf',
    SupplierStatementPdfController::class
)
    ->middleware([
        'signed',
        'throttle:30,1',
    ])
    ->name('supplier-statements.pdf');