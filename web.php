<?php
// routes/web.php

// cd /opt/lampp/htdocs/laravel_auction && php artisan serve

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ViewsController;

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

Route::get('/', [ViewsController::class, 'sellers']);
Route::get('/lots/{id}', [ViewsController::class, 'lots']);
