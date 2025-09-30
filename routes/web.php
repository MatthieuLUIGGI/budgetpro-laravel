<?php

use App\Http\Controllers\TransactionController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    return view('app');
})->middleware('auth');

Route::prefix('api')->middleware('auth')->group(function () {
    Route::get('/transactions', [TransactionController::class, 'index']);
    Route::post('/transactions', [TransactionController::class, 'store']);
    Route::delete('/transactions/{id}', [TransactionController::class, 'destroy']);
    Route::get('/dashboard', [TransactionController::class, 'dashboard']);
});

Route::middleware('auth')->group(function() {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Auth::routes();

// Pages statiques (auth facultatif selon besoin; ici accessible uniquement aux utilisateurs connectés pour cohérence)
Route::middleware('auth')->group(function () {
    Route::view('/confidentialite', 'legal.privacy')->name('privacy');
    Route::view('/conditions', 'legal.terms')->name('terms');
    Route::get('/support', function() { return view('support.index'); })->name('support');
    Route::post('/support', function(\Illuminate\Http\Request $request) {
        $data = $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:5000',
        ]);
        // Placeholder: envoi email ou stockage futur
        return back()->with('status','Message envoyé. Nous vous répondrons bientôt.');
    })->name('support.send');
});