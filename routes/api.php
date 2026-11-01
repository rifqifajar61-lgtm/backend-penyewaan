<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AlatController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\PelangganController;
use App\Http\Controllers\PelangganDataController;
use App\Http\Controllers\PenyewaanController;
use App\Http\Controllers\PenyewaanDetailController;
use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| AUTH ADMIN
|--------------------------------------------------------------------------
*/

Route::post('auth/register', [AuthController::class, 'register']);
Route::post('auth/login', [AuthController::class, 'login']);
Route::get('auth/me', [AuthController::class, 'me']);
Route::post('auth/logout', [AuthController::class, 'logout']);


/*
|--------------------------------------------------------------------------
| AUTH PELANGGAN
|--------------------------------------------------------------------------
*/

// Register pelanggan
Route::post(
    'pelanggan/register',
    [PelangganController::class, 'register']
);

// Login pelanggan
Route::post(
    'pelanggan/login',
    [PelangganController::class, 'login']
);


/*
|--------------------------------------------------------------------------
| CRUD
|--------------------------------------------------------------------------
*/

// Pelanggan
Route::apiResource(
    'pelanggan',
    PelangganController::class
);

// Pelanggan Data
Route::apiResource(
    'pelanggan-data',
    PelangganDataController::class
);

// Admin
Route::apiResource(
    'admin',
    AdminController::class
);

// Kategori
Route::apiResource(
    'kategori',
    KategoriController::class
);

// Alat
Route::apiResource(
    'alat',
    AlatController::class
);

// Penyewaan
Route::apiResource(
    'penyewaan',
    PenyewaanController::class
);

// Penyewaan Detail
Route::apiResource(
    'penyewaan-detail',
    PenyewaanDetailController::class
);

