<?php

use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\ContactMessageController as AdminContactMessageController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ExperienceController;
use App\Http\Controllers\Admin\ProjectController as AdminProjectController;
use App\Http\Controllers\Admin\ProjectMediaController;
use App\Http\Controllers\Admin\SiteSettingController;
use App\Http\Controllers\Admin\SkillController;
use App\Http\Controllers\Auth\AdminLoginController;
use App\Http\Controllers\ContactMessageController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\PortfolioController;
use App\Http\Controllers\SeoController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PortfolioController::class, 'index'])->name('portfolio.index');
Route::get('/projects/{project}', [PortfolioController::class, 'show'])->name('portfolio.projects.show');

Route::post('/contact', [ContactMessageController::class, 'store'])
    ->middleware('throttle:contact')
    ->name('portfolio.contact.store');

Route::get('/sitemap.xml', [SeoController::class, 'sitemap'])->name('seo.sitemap');
Route::get('/robots.txt', [SeoController::class, 'robots'])->name('seo.robots');
Route::get('/site.webmanifest', [SeoController::class, 'manifest'])->name('seo.manifest');

Route::get('/health', HealthController::class)
    ->middleware('throttle:health')
    ->name('health.readiness');

Route::prefix('admin')->name('admin.')->group(function (): void {
    Route::middleware('guest')->group(function (): void {
        Route::get('/login', [AdminLoginController::class, 'create'])->name('login');
        Route::post('/login', [AdminLoginController::class, 'store'])
            ->middleware('throttle:admin-login')
            ->name('login.store');
    });

    Route::middleware(['auth', 'admin', 'audit.admin', 'throttle:admin'])->group(function (): void {
        Route::get('/', DashboardController::class)->name('dashboard');

        Route::resource('projects', AdminProjectController::class)->except('show');
        Route::post('/projects/{project}/media', [ProjectMediaController::class, 'store'])
            ->middleware('throttle:admin-upload')
            ->name('projects.media.store');
        Route::put('/projects/{project}/media/{media}', [ProjectMediaController::class, 'update'])->name('projects.media.update');
        Route::delete('/projects/{project}/media/{media}', [ProjectMediaController::class, 'destroy'])->name('projects.media.destroy');
        Route::post('/projects/{project}/media/reorder', [ProjectMediaController::class, 'reorder'])->name('projects.media.reorder');

        Route::resource('skills', SkillController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::resource('experiences', ExperienceController::class)->except('show');

        Route::get('/settings', [SiteSettingController::class, 'edit'])->name('settings.edit');
        Route::put('/settings', [SiteSettingController::class, 'update'])->name('settings.update');

        Route::get('/messages', [AdminContactMessageController::class, 'index'])->name('messages.index');
        Route::get('/messages/{message}', [AdminContactMessageController::class, 'show'])->name('messages.show');
        Route::post('/messages/{message}/reply', [AdminContactMessageController::class, 'reply'])->name('messages.reply');
        Route::patch('/messages/{message}/archive', [AdminContactMessageController::class, 'archive'])->name('messages.archive');
        Route::delete('/messages/{message}', [AdminContactMessageController::class, 'destroy'])->name('messages.destroy');

        Route::get('/activity', [ActivityLogController::class, 'index'])->name('activity.index');

        Route::post('/logout', [AdminLoginController::class, 'destroy'])->name('logout');
    });
});
