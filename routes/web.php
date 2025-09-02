<?php

use App\Http\Controllers\BarangController;
use App\Http\Controllers\ImageController;
use App\Livewire\Auth\Login;
use App\Livewire\Barang\Index;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', Login::class)->name('login');
});

// Rute untuk pengguna yang sudah login
Route::middleware('auth')->group(function () {
    // Halaman utama setelah login
    Route::get('/', Index::class)->name('home');

    // Route BARU untuk menampilkan gambar yang aman
    Route::get('/images/{filename}', [ImageController::class, 'show'])->name('images.show');

    Route::get('/barang/{id}/download', [BarangController::class, 'downloadImage'])->name('barang.download');

    // Rute untuk proses logout
    Route::post('/logout', function () {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect('/login');
    })->name('logout');
});
