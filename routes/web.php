<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Route;

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

// Admin Routes
Route::prefix('admin')->group(function () {
    Route::get('/login', [AdminController::class, 'showLoginForm'])->name('admin.login');
    Route::post('/login', [AdminController::class, 'login'])->name('admin.login.submit');
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::post('/logout', [AdminController::class, 'logout'])->name('admin.logout');
    Route::get('/', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/etudiantDetails/{id}', [AdminController::class, 'etudiantDetails'])->name('admin.etudiantDetails');
    Route::post('/etudiantDetails/{id}/update-password', [AdminController::class, 'updateEtudiantPassword'])->name('admin.updateEtudiantPassword');
    Route::get('/etudiants/{etudiantId}/filieres/{filiereId}/payments/{year}', [AdminController::class, 'getMonthlyPayments'])->name('admin.getMonthlyPayments');
    Route::post('/monthly-enrollment/add', [AdminController::class, 'addMonthlyEnrollment'])->name('admin.addMonthlyEnrollment');
    Route::post('/monthly-enrollment/delete', [AdminController::class, 'deleteMonthlyEnrollment'])->name('admin.deleteMonthlyEnrollment');
    
    // Filiere enrollment routes
    Route::get('/etudiantDetails/{id}/inscription-filiere', [AdminController::class, 'showInscriptionFiliereForm'])->name('admin.inscriptionFiliere');
    Route::post('/etudiantDetails/{id}/inscription-filiere', [AdminController::class, 'storeInscriptionFiliere'])->name('admin.storeInscriptionFiliere');
    
    // Update filiere status
    Route::post('/update-filiere-status', [AdminController::class, 'updateFiliereStatus'])->name('admin.updateFiliereStatus');
});

require __DIR__.'/auth.php';
