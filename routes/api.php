<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Dashboard\BannerController;
use App\Http\Controllers\Dashboard\InformationController;
use App\Http\Controllers\Dashboard\ProductCategoryController;
use App\Http\Controllers\Dashboard\ProductController;
use App\Http\Controllers\Dashboard\RewardController;
use App\Http\Controllers\Dashboard\WebConfigController;
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

Route::group(["middleware" => "guest"], function () {
    Route::post("/auth/register", [AuthController::class, "register"]);
    Route::post("/auth/login", [AuthController::class, "validateLogin"]);
});

Route::group(["middleware" => "check.auth"], function () {
    // ADMIN AND RESELLER ACCESS

    // ONLY ADMIN ACCESS
    Route::group(["middleware" => "api.check.role:ADMIN"], function () {
        Route::post("/config/create-update", [WebConfigController::class, "saveUpdateData"])->name('web.update-config');

        // PREFIX MASTER
        Route::group(["prefix" => "master"], function () {
            // BANNER
            Route::group(["prefix" => "banner"], function () {
                Route::get("datatable", [BannerController::class, "dataTable"])->name('banner.datatable');
                Route::get("{id}/detail", [BannerController::class, "getDetail"])->name('banner.detail');
                Route::post("create", [BannerController::class, "create"])->name('banner.create');
                Route::post("update", [BannerController::class, "update"])->name('banner.update');
                Route::post("update-status", [BannerController::class, "updateStatus"])->name('banner.change-status');
                Route::delete("delete", [BannerController::class, "destroy"])->name('banner.destroy');
            });

            // INFORMATION
            Route::group(["prefix" => "information"], function () {
                Route::get("datatable", [InformationController::class, "dataTable"])->name('information.datatable');
                Route::get("{id}/detail", [InformationController::class, "getDetail"])->name('information.detail');
                Route::post("create", [InformationController::class, "create"])->name('information.create');
                Route::post("update", [InformationController::class, "update"])->name('information.update');
                Route::post("update-status", [InformationController::class, "updateStatus"])->name('information.change-status');
                Route::delete("delete", [InformationController::class, "destroy"])->name('information.destroy');
            });

            // PRODUCT CATEGORY
            Route::group(["prefix" => "product-category"], function () {
                Route::get("datatable", [ProductCategoryController::class, "dataTable"])->name('product-category.datatable');
                Route::get("{id}/detail", [ProductCategoryController::class, "getDetail"])->name('product-category.detail');
                Route::post("create", [ProductCategoryController::class, "create"])->name('product-category.create');
                Route::post("update", [ProductCategoryController::class, "update"])->name('product-category.update');
                Route::post("update-status", [ProductCategoryController::class, "updateStatus"])->name('product-category.change-status');
                Route::delete("delete", [ProductCategoryController::class, "destroy"])->name('product-category.destroy');
            });

            // PRODUCT
            Route::group(["prefix" => "product"], function () {
                Route::get("datatable", [ProductController::class, "dataTable"])->name('product.datatable');
                Route::get("{id}/detail", [ProductController::class, "getDetail"])->name('product.detail');
                Route::post("create", [ProductController::class, "create"])->name('product.create');
                Route::post("update", [ProductController::class, "update"])->name('product.update');
                Route::post("update-status", [ProductController::class, "updateStatus"])->name('product.change-status');
                Route::delete("delete", [ProductController::class, "destroy"])->name('product.destroy');
            });

            // REWARD
            Route::group(["prefix" => "reward"], function () {
                Route::get("datatable", [RewardController::class, "dataTable"])->name('reward.datatable');
                Route::get("{id}/detail", [RewardController::class, "getDetail"])->name('reward.detail');
                Route::post("create", [RewardController::class, "create"])->name('reward.create');
                Route::post("update", [RewardController::class, "update"])->name('reward.update');
                Route::post("update-status", [RewardController::class, "updateStatus"])->name('reward.change-status');
                Route::delete("delete", [RewardController::class, "destroy"])->name('reward.destroy');
            });
        });
    });
});
