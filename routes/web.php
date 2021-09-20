<?php

use Illuminate\Support\Facades\Route;

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

Route::get('/', function () {
    return view('home');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::resource("users", "App\Http\Controllers\UserController");

Route::get("user_licenses/edit", "App\Http\Controllers\UserLicenseController@edit")->name("user_licenses.edit");
Route::patch("user_licenses/update", "App\Http\Controllers\UserLicenseController@update")->name("user_licenses.update");
Route::get("user_licenses/create", "App\Http\Controllers\UserLicenseController@create")->name("user_licenses.create");
Route::post("user_licenses", "App\Http\Controllers\UserLicenseController@store")->name("user_licenses.store");

Route::get("/user_pays/create", "App\Http\Controllers\UserPayController@create")->name("user_pays.create");
Route::post("/user_pays", "App\Http\Controllers\UserPayController@store")->name("user_pays.store");
Route::get("/user_pays/edit", "App\Http\Controllers\UserPayController@edit")->name("user_pays.edit");
Route::patch("user_pays/update", "App\Http\Controllers\UserPayController@update")->name("user_pays.update");

Route::get("/user_papers/create", "App\Http\Controllers\UserPaperController@create")->name("user_papers.create");
Route::post("/user_papers", "App\Http\Controllers\UserPaperController@store")->name("user_papers.store");
Route::get("/user_papers/edit", "App\Http\Controllers\UserPaperController@edit")->name("user_papers.edit");
Route::patch("/user_papers/update", "App\Http\Controllers\UserPaperController@update")->name("user_papers.update");

Route::resource("memos", "App\Http\Controllers\MemoController");