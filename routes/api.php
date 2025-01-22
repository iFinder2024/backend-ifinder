<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CadastrastraUserController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\TokenSisController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('/login', [AuthController::class, 'login'])->name('login');
// Route::middleware('auth.partner')->post('/me', [AuthController::class, 'me']);

Route::group(['middleware' => 'auth.jwt'], function () {
    Route::post('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/token/generate', [TokenSisController::class, 'geratoken']);

    // CADASTRAR
    Route::prefix('register')->group(function () {
        // CRUD USUÁRIO
        Route::post('/user', [CadastrastraUserController::class, 'cadastraUsuario']);
        Route::post('/admin', [CadastrastraUserController::class, 'cadastraUsuarioAdmin']);

        // CRUD COMPANY
        Route::post('/register/company', [CompanyController::class, 'CadastraCompany']);
    });
});
