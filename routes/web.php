<?php

use App\Http\Controllers\ArticleController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QuoteRequestController;
use App\Http\Controllers\Webhooks\BankVirtualAccountWebhookController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', [PageController::class, 'home'])->name('home');
Route::get('/solutions', [PageController::class, 'solutions'])->name('solutions.index');
Route::get('/education', [PageController::class, 'education'])->name('education.index');
Route::get('/contact', [PageController::class, 'contact'])->name('contact.index');
Route::post('/contact', [ContactController::class, 'store'])->name('contact.store');

Route::get('/services', [PageController::class, 'services'])->name('services.index');
Route::get('/services/{slug}', [PageController::class, 'serviceShow'])->name('services.show');

Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/{id}', [ProductController::class, 'show'])->name('products.show');

Route::get('/insights/news', [ArticleController::class, 'index'])->name('news.index');
Route::get('/insights/news/{slug}', [ArticleController::class, 'show'])->name('news.show');

Route::post('/quote-request', [QuoteRequestController::class, 'store'])->name('quote.store');

Route::post('/webhooks/bank-virtual-account', BankVirtualAccountWebhookController::class)
    ->name('webhooks.bank.virtual_account');

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('/language/{locale}', function ($locale) {
    if (in_array($locale, ['en', 'vi'])) {
        session()->put('locale', $locale);
    }

    return redirect()->back();
})->name('language.switch');

Route::middleware('auth')->prefix('ops')->group(function () {
    Route::get('/demand', function () {
        return redirect('/ops/demand-workspace', 302);
    });

    Route::get('/orders/{path?}', function (?string $path = null) {
        $targetPath = '/ops/demand/orders'.($path !== null && $path !== '' ? '/'.$path : '');
        $queryString = request()->getQueryString();

        return redirect($queryString !== null ? $targetPath.'?'.$queryString : $targetPath, 302);
    })->where('path', '.*');

    Route::get('/contracts/{path?}', function (?string $path = null) {
        $targetPath = '/ops/demand/contracts'.($path !== null && $path !== '' ? '/'.$path : '');
        $queryString = request()->getQueryString();

        return redirect($queryString !== null ? $targetPath.'?'.$queryString : $targetPath, 302);
    })->where('path', '.*');
});

require __DIR__.'/auth.php';
