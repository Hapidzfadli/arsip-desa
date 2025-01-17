<?php

use Inertia\Inertia;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Application;
use App\Http\Controllers\UserController;
use App\Http\Controllers\BagianController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SuratMasukController;
use App\Http\Controllers\SuratKeluarController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Public routes
Route::get('/', function () {
    return Inertia::render('Auth/Login');
})->middleware('guest')->name('login');

// Protected routes
Route::middleware(['auth'])->group(function () {
    // Dashboard/Beranda
    Route::get('/beranda', [UserController::class, 'index'])->name('users.index');

    // Profile routes
    Route::get('/users/profile', [UserController::class, 'profile'])->name('users.profile');
    Route::post('/users/profile/update', [UserController::class, 'updateProfile'])->name('users.profile.update');
    Route::post('/users/profile/password', [UserController::class, 'updatePassword'])->name('users.profile.password');

    // User management routes (admin only)
    Route::middleware(['admin'])->group(function () {
        Route::get('/users/pengguna', [UserController::class, 'pengguna'])->name('users.pengguna');
        Route::get('/users/pengguna/t', [UserController::class, 'createPengguna'])->name('users.pengguna.create');
        Route::post('/users/pengguna', [UserController::class, 'storePengguna'])->name('users.pengguna.store');
        Route::get('/users/pengguna/e/{user}', [UserController::class, 'editPengguna'])->name('users.pengguna.edit');
        Route::put('/users/pengguna/{user}', [UserController::class, 'updatePengguna'])->name('users.pengguna.update');
        Route::get('/users/pengguna/h/{user}', [UserController::class, 'destroyPengguna'])->name('users.pengguna.destroy');
    });

    // Surat Masuk routes
    Route::resource('surat-masuk', SuratMasukController::class);
    Route::post('surat-masuk/{suratMasuk}/disposisi', [SuratMasukController::class, 'toggleDisposisi'])
        ->name('surat-masuk.disposisi');
    Route::get('surat-masuk/{suratMasuk}/download-lampiran', [SuratMasukController::class, 'downloadLampiran'])
        ->name('surat-masuk.download-lampiran');

    // Surat Keluar routes
    Route::resource('surat-keluar', SuratKeluarController::class);
    Route::post('surat-keluar/{suratKeluar}/disposisi', [SuratKeluarController::class, 'toggleDisposisi'])
        ->name('surat-keluar.disposisi');
    Route::post('surat-keluar/{suratKeluar}/peringatan', [SuratKeluarController::class, 'togglePeringatan'])
        ->name('surat-keluar.peringatan');
    Route::get('surat-keluar/{suratKeluar}/download-lampiran', [SuratKeluarController::class, 'downloadLampiran'])
        ->name('surat-keluar.download-lampiran');

    // Bagian routes (admin/s_admin only)
    Route::middleware(['can:manage-bagian'])->group(function () {
        Route::resource('bagian', BagianController::class);
    });

    // Report routes
    Route::get('laporan/surat-masuk', [SuratMasukController::class, 'report'])->name('laporan.surat-masuk');
    Route::get('laporan/data-sm/{tgl1}/{tgl2}', [SuratMasukController::class, 'dataSm'])->name('laporan.data-sm');
    Route::get('laporan/cetak-sm/{tgl1}/{tgl2}', [SuratMasukController::class, 'cetakSm'])->name('laporan.cetak-sm');

    Route::get('laporan/surat-keluar', [SuratKeluarController::class, 'report'])->name('laporan.surat-keluar');
    Route::get('laporan/data-sk/{tgl1}/{tgl2}', [SuratKeluarController::class, 'dataSk'])->name('laporan.data-sk');
    Route::get('laporan/cetak-sk/{tgl1}/{tgl2}', [SuratKeluarController::class, 'cetakSk'])->name('laporan.cetak-sk');
});

require __DIR__ . '/auth.php';
