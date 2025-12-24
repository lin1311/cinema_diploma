<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\HallController;
use App\Http\Controllers\Admin\HallSchemeController;
use App\Http\Controllers\Admin\SetPricesController;
use App\Http\Controllers\Admin\MovieController;

Route::get('/', function () {
    return view('welcome');
});


// Главная админки (выводит залы — HallController)
Route::get('/admin', [HallController::class, 'index'])->name('admin.halls.index');

// CRUD залов
Route::get('/admin/halls', [HallController::class, 'index'])->name('admin.halls.index');
Route::post('/admin/halls', [HallController::class, 'store'])->name('admin.halls.store');
Route::delete('/admin/halls/{id}', [HallController::class, 'destroy'])->name('admin.halls.destroy');

// Работа со схемами залов
Route::get('/admin/halls/{hall}/scheme', [HallSchemeController::class, 'show'])->name('admin.halls.scheme.show');

Route::post('/admin/halls/{hall}/scheme', [HallSchemeController::class, 'save'])->name('admin.halls.scheme.save');

// Группа маршрутов для админки, раздел "Конфигурация цен"
Route::prefix('admin/prices')->name('admin.prices.')->group(function () {
    Route::get('/', [SetPricesController::class, 'index'])->name('index');
    Route::post('/{hall}', [SetPricesController::class, 'update'])->name('update');
});

Route::prefix('admin')->group(function () {
    // Список фильмов
    Route::get('movies', [MovieController::class, 'index'])->name('admin.movies.index');
    // Форма создания
    Route::get('movies/create', [MovieController::class, 'create'])->name('movies.create');
    // Сохранение фильма
    Route::post('movies', [MovieController::class, 'store'])->name('movies.store');
    // Форма редактирования
    Route::get('movies/{movie}/edit', [MovieController::class, 'edit'])->name('movies.edit');
    // Обновление фильма
    Route::put('movies/{movie}', [MovieController::class, 'update'])->name('movies.update');
    // Удаление фильма
    Route::delete('movies/{movie}', [MovieController::class, 'destroy'])->name('movies.destroy');
});