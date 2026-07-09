<?php

use App\Http\Controllers\DenunciaController;
use App\Http\Controllers\EmisorController;
use App\Http\Controllers\EstadoController;
use App\Http\Controllers\PalabrasClavesController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\TipoDenunciaController;
use App\Http\Controllers\TipoEmisorController;
use App\Http\Controllers\TipoRedSocialController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use App\Livewire\Admin\Dashboard;

//Route::view('/', 'welcome')->name('home');

Route::redirect('/', '/login');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/admin', Dashboard::class)->name('dashboard');

    Route::redirect('/dashboard', '/admin');

    Route::prefix('tipo_denuncia')->name('tipo_denuncia.')->group(function () {
        Route::get('papelera', [TipoDenunciaController::class, 'papelera'])->name('papelera');
        Route::put('{id}/restore', [TipoDenunciaController::class, 'restore'])->name('restore');
        Route::put('restore-all', [TipoDenunciaController::class, 'restoreAll'])->name('restoreAll');
    });

    Route::resource('tipo_denuncia', TipoDenunciaController::class)
        ->except(['show'])
        ->parameters(['tipo_denuncia' => 'tipo_denuncia']);

    Route::prefix('estado')->name('estado.')->group(function () {
        Route::get('papelera', [EstadoController::class, 'papelera'])->name('papelera');
        Route::put('{id}/restore', [EstadoController::class, 'restore'])->name('restore');
        Route::put('restore-all', [EstadoController::class, 'restoreAll'])->name('restoreAll');
    });

    Route::resource('estado', EstadoController::class)->except(['show']);

    Route::prefix('tipo_emisor')->name('tipo_emisor.')->group(function () {
        Route::get('papelera', [TipoEmisorController::class, 'papelera'])->name('papelera');
        Route::put('{id}/restore', [TipoEmisorController::class, 'restore'])->name('restore');
        Route::put('restore-all', [TipoEmisorController::class, 'restoreAll'])->name('restoreAll');
    });

    Route::resource('tipo_emisor', TipoEmisorController::class)
        ->except(['show'])
        ->parameters(['tipo_emisor' => 'tipo_emisor']);
   

    Route::resource('denuncia', DenunciaController::class);
    
    Route::prefix('emisor')->name('emisor.')->group(function () {
        Route::get('papelera', [EmisorController::class, 'papelera'])->name('papelera');
        Route::put('{id}/restore', [EmisorController::class, 'restore'])->name('restore');
        Route::put('restore-all', [EmisorController::class, 'restoreAll'])->name('restoreAll');
    });

    Route::resource('emisor', EmisorController::class)
        ->parameters(['emisor' => 'emisor']);

    Route::prefix('tipo_red_social')->name('tipo_red_social.')->group(function () {
        Route::get('papelera', [TipoRedSocialController::class, 'papelera'])->name('papelera');
        Route::put('{id}/restore', [TipoRedSocialController::class, 'restore'])->name('restore');
        Route::put('restore-all', [TipoRedSocialController::class, 'restoreAll'])->name('restoreAll');
    });

    Route::resource('tipo_red_social', TipoRedSocialController::class)
        ->except(['show'])
        ->parameters(['tipo_red_social' => 'tipo_red_social']);

    Route::prefix('palabras_claves')->name('palabras_clave.')->group(function () {
        Route::get('papelera', [PalabrasClavesController::class, 'papelera'])->name('papelera');
        Route::put('{id}/restore', [PalabrasClavesController::class, 'restore'])->name('restore');
        Route::put('restore-all', [PalabrasClavesController::class, 'restoreAll'])->name('restoreAll');
    });

    Route::resource('palabras_claves', PalabrasClavesController::class)->parameters([
        'palabras_claves' => 'palabras_clave'
    ])->names('palabras_clave');

    Route::resource('user', UserController::class);

    Route::resource('permission', PermissionController::class);
    Route::resource('role', RoleController::class);



});

require __DIR__.'/settings.php';