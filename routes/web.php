<?php


use App\Http\Controllers\UserLicenseController;
use App\Http\Controllers\UserPayController;
use App\Http\Controllers\UserPaperController;
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

Route::resource("users", UserController::class);

Route::get("user_licenses/edit", [UserLicenseController::class, "edit"])->name("user_licenses.edit");
Route::patch("user_licenses/update", [UserLicenseController::class, "update"])->name("user_licenses.update");
Route::get("user_licenses/create", [UserLicenseController::class, "create"])->name("user_licenses.create");
Route::post("user_licenses", [UserLicenseController::class, "store"])->name("user_licenses.store");

Route::get("/user_pays/create", [UserPayController::class, "create"])->name("user_pays.create");
Route::post("/user_pays", [UserPayController::class, "store"])->name("user_pays.store");
Route::get("/user_pays/edit", [UserPayController::class, "edit"])->name("user_pays.edit");
Route::patch("user_pays/update", [UserPayController::class, "update"])->name("user_pays.update");

Route::get("/user_papers/create", [UserPaperController::class, "create"])->name("user_papers.create");
Route::post("/user_papers", [UserPaperController::class, "store"])->name("user_papers.store");
Route::get("/user_papers/edit", [UserPaperController::class, "edit"])->name("user_papers.edit");
Route::patch("/user_papers/update", [UserPaperController::class, "update"])->name("user_papers.update");

Route::resource("memos", MemoController::class);