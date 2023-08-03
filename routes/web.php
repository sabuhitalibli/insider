<?php

use App\Http\Controllers\MainController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', [MainController::class, 'index']);
Route::get('/current-week', [MainController::class, 'currentWeek'])->name('current_week');
Route::get('/next-week', [MainController::class, 'nextWeek'])->name('next_week');
Route::get('/play-all', [MainController::class, 'playAll'])->name('play_all');
