<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Dashboard\BannerController;
use App\Http\Controllers\Dashboard\InformationController;
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
        });
    });
});
