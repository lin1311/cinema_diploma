<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\HallController;
use App\Http\Controllers\Admin\HallSchemeController;
use App\Http\Controllers\Admin\SetPricesController;
use App\Http\Controllers\Admin\MovieController;
use App\Http\Controllers\Admin\SeanceController;
use App\Http\Controllers\Admin\PublicationController;
use App\Http\Controllers\Client\ClientController;
use App\Http\Controllers\SeanceScheduleController;

Route::get('/', [ClientController::class, 'index'])->name('client.index');
Route::get('/hall/{seance}', [ClientController::class, 'hall'])->name('client.hall');
Route::get('/seances', [SeanceScheduleController::class, 'index'])->name('seances.index');
Route::get('/payment/{seance}', [ClientController::class, 'payment'])->name('client.payment');
Route::post('/hall/{seance}/seats', [ClientController::class, 'reserveSeats'])->name('client.hall.reserve');

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

    Route::post('seances', [SeanceController::class, 'store'])->name('admin.seances.store');
    Route::post('publications', [PublicationController::class, 'store'])->name('admin.publications.store');
});