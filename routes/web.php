<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    AuthController,
    CartController,
    ObatController,
    AdminController,
    KasirController,
    MemberController,
    ProfileController,
    CategoryController,
    DashboardController
};

// ==============================
// Public Routes
// ==============================
Route::get('/', function () {
    return view('welcome', [
        'title'   => 'Welcome',
        'project' => 'Apotek'
    ]);
})->name('home');

// ==============================
// Auth Routes
// ==============================
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ==============================
// Protected Routes (hanya user login)
// ==============================
Route::middleware(['auth'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/data', [DashboardController::class, 'getChartData'])->name('dashboard.data');
    Route::get('/dashboard/data/{year}', [DashboardController::class, 'getChartDataByYear']);
    Route::get('/export/pdf/month', [DashboardController::class, 'exportPdfMonth'])->name('dashboard.export.pdf.month');
    Route::get('/export/pdf/year', [DashboardController::class, 'exportPdfYear'])->name('dashboard.export.pdf.year');
    Route::get('/export/pdf/all', [DashboardController::class, 'exportPdfAll'])->name('dashboard.export.pdf.all');

    // Master Data
    Route::resource('obat', ObatController::class);
    Route::resource('category', CategoryController::class);
    Route::resource('kasir', KasirController::class);
    Route::resource('members', MemberController::class);
    Route::get('/member/search', [MemberController::class, 'search'])->name('member.search');

    // Cart & Transactions
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/scan', [CartController::class, 'scan'])->name('cart.scan');
    Route::post('/checkout', [CartController::class, 'checkout'])->name('checkout');
    Route::delete('/cart/item/{item}', [CartController::class, 'removeItem'])->name('cart.remove');
    Route::delete('/cart/item/{id}/remove', [CartController::class, 'deleteItem'])->name('cart.item.delete');
    Route::put('/cart/item/{item}', [CartController::class, 'updateItem'])->name('cart.item.update');
    Route::post('/cart/item/{item}/update', [CartController::class, 'updateQuantity'])->name('cart.item.update');
    Route::patch('/cart/item/{id}/toggle-check', [CartController::class, 'toggleCheck'])->name('cart.toggleCheck');
    Route::post('/cart/{cart}/clear-expired', [CartController::class, 'clearExpired'])->name('cart.clear-expired');
    Route::post('/cart/{cart}/sendWa', [CartController::class, 'sendWhatsappMessage'])->name('cart.sendWhatsApp');
    Route::get('/cart/transactions', [CartController::class, 'transaction'])->name('transaction.history');
    Route::get('/transaction/{transaction}/detail', [CartController::class, 'transactionDetail'])->name('transaction.detail');

    // Invoice
    Route::get('/order/{order}/invoice', [CartController::class, 'invoiceShow'])->name('order.invoice');
    Route::get('/order/{order}/invoice/download', [CartController::class, 'invoiceDownload'])->name('order.invoice.download');
    Route::get('/orders/download/pdf', [CartController::class, 'downloadAllTransactions'])->name('orders.download.pdf');

    // Profile
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // Admin Page
    Route::get('/admin', [AdminController::class, 'index'])->name('admin.index');
});

// ==============================
// Role Based Routes
// ==============================
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/dashboard', function () {
        return 'Halaman Admin';
    });
});

Route::middleware(['auth', 'role:kasir'])->group(function () {
    Route::get('/kasir/dashboard', function () {
        return 'Halaman Kasir';
    });
});
