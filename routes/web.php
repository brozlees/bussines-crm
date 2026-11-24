<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LeadReviewController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth'])->prefix('leads')->name('leads.')->group(function () {
    Route::get('/', [LeadReviewController::class, 'index'])->name('index');
    Route::get('/{lead}', [LeadReviewController::class, 'show'])->name('show');
    Route::post('/{lead}/approve', [LeadReviewController::class, 'approve'])->name('approve');
    Route::post('/{lead}/reject', [LeadReviewController::class, 'reject'])->name('reject');
    Route::post('/{lead}/mark-as-sent', [LeadReviewController::class, 'markAsSent'])->name('mark-as-sent');
    Route::post('/bulk-approve', [LeadReviewController::class, 'bulkApprove'])->name('bulk-approve');
    Route::post('/bulk-reject', [LeadReviewController::class, 'bulkReject'])->name('bulk-reject');
});

require __DIR__.'/auth.php';
