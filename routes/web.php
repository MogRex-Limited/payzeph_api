<?php

use App\Http\Controllers\Web\IndexController;
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

Route::get('/', function () {
    return view('welcome');
});

Route::as("web.")->group(function () {
    Route::get('file/{path}', [IndexController::class, "read_file"])->name("read_file");
    Route::get("phonebook/export-csv", [IndexController::class, "exportCsv"])->name("export.csv");
});
