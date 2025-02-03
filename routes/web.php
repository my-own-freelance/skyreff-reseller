<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Dashboard\BankController;
use App\Http\Controllers\Dashboard\BannerController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Dashboard\InformationController;
use App\Http\Controllers\Dashboard\MutationController;
use App\Http\Controllers\Dashboard\ProductCategoryController;
use App\Http\Controllers\Dashboard\ProductController;
use App\Http\Controllers\Dashboard\RewardController;
use App\Http\Controllers\Dashboard\ResellerController;
use App\Http\Controllers\Dashboard\TrxProductController;
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

Route::get("/logout", [AuthController::class, "logout"])->name("logout");

// AUTH
Route::group(["middleware" => "guest"], function () {
    Route::get('/login', [AuthController::class, 'login'])->name('login');
});

// DASHBOARD
Route::group(["middleware" => "auth:web"], function () {
    Route::get("/admin", [DashboardController::class, "index"])->name("dashboard.admin");
    Route::get("/reseller", [DashboardController::class, "index"])->name("dashboard.reseller");
    Route::get("/trx/product", [TrxProductController::class, "index"])->name("trx-product");
    Route::get("/mutation/commission", [MutationController::class, "index"])->name("mutation-commission");

    // GLOBAL ACCESS
    Route::get("/master/products", [ProductController::class, 'index'])->name('product');

    // ONLY ADMIN ACCESS
    Route::group(["middleware" => "api.check.role:ADMIN"], function () {
        // PREFIX MASTER
        Route::group(["prefix" => "master"], function () {
            Route::get("/bank", [BankController::class, 'index'])->name('bank');
            Route::get("/banner", [BannerController::class, 'index'])->name('banner');
            Route::get("/information", [InformationController::class, 'index'])->name('information');
            Route::get("/product-category", [ProductCategoryController::class, 'index'])->name('product-category');
            Route::get("/reward", [RewardController::class, 'index'])->name('reward');
        });

        // PREFIX MANAGE
        Route::group(["prefix" => "manage"], function () {
            // RESELLER
            Route::group(["prefix" => "reseller"], function () {
                Route::get("/", [ResellerController::class, 'indexReseller'])->name("reseller");
                Route::get("/waiting", [ResellerController::class, 'resellerWaiting'])->name("reseller.waiting");
                Route::get("/regular", [ResellerController::class, 'resellerRegular'])->name("reseller.regular");
                Route::get("/vip", [ResellerController::class, 'resellerVip'])->name("reseller.vip");
                Route::get("/deleted", [ResellerController::class, 'resellerDeleted'])->name("reseller.deleted");
            });
        });
    });

    // ONLY RESELLER

});
