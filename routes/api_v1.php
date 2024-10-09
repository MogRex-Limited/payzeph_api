<?php

use App\Http\Controllers\Api\V1\Auth\User\TwoFactorController;
use App\Http\Controllers\Api\V1\Auth\User\UserLoginController;
use App\Http\Controllers\Api\V1\Auth\User\UserPasswordController;
use App\Http\Controllers\Api\V1\Auth\User\UserRegisterController;
use App\Http\Controllers\Api\V1\Auth\User\UserVerificationController;
use App\Http\Controllers\Api\V1\General\GeneralController;
use App\Http\Controllers\Api\V1\Location\LocationController;
use App\Http\Controllers\Api\V1\User\Finance\Payment\PaymentController;
use App\Http\Controllers\Api\V1\User\Finance\Transaction\TransactionController;
use App\Http\Controllers\Api\V1\User\Setting\ApiCredentialController;
use App\Http\Controllers\Api\V1\User\Setting\ProfileController;
use Illuminate\Support\Facades\Route;

Route::middleware(["apiKey"])->group(function () {
    Route::prefix("user")->as("user.")->group(function () {
        Route::post("account/preview", [GeneralController::class, "previewUser"])->name("preview-user");

        Route::prefix("auth")->as("auth.")->group(function () {
            Route::post("/register", [UserRegisterController::class, "register"])->name("register");
            Route::post("/login", [UserLoginController::class, "login"])->name("login");
            Route::post("/logout", [UserLoginController::class, "logout"])->name("logout")->middleware(["auth:sanctum"]);
            Route::get("account/fetch", [UserLoginController::class, "fetchAccounts"])->name("login_preview")->middleware(["auth:sanctum"]);

            Route::prefix("password")->as("password.")->group(function () {
                Route::post('/forgot', [UserPasswordController::class, 'forgotPassword'])->name("forgot_password");
                Route::post("/reset", [UserPasswordController::class,  "resetPassword"])->name("reset_password");
            });

            Route::middleware(["auth:sanctum"])->prefix("otp")->as("otp.")->group(function () {
                Route::post('/request', [UserVerificationController::class, 'request'])->name("request");
                Route::post("/verify", [UserVerificationController::class,  "verify"])->name("verify");
            });
        });

        Route::middleware(["auth:sanctum"])->group(function () {
            
            Route::get('/2fa/generate-secret', [TwoFactorController::class, 'generateSecretKey']);
            Route::post('/2fa/enable', [TwoFactorController::class, 'enable2FA']);
            Route::post('/2fa/disable', [TwoFactorController::class, 'disable2FA']);

            Route::prefix("transactions")->as("transactions.")->group(function () {
                Route::get("/", [TransactionController::class, "index"]);
                Route::get("{transaction}/show", [TransactionController::class, "show"]);
            });

            Route::apiResources([
                
            ]);

            Route::prefix("payments")->as("payments.")->group(function () {
                Route::post("initiate", [PaymentController::class, "initiate"])->name("initiate");
            });

            Route::prefix("settings")->as("settings.")->group(function () {
                Route::prefix("profile")->as("profile.")->group(function () {
                    Route::get("me", [ProfileController::class, "show"]);
                    Route::post("update-profile", [ProfileController::class, "update"]);
                    Route::post("update-password", [ProfileController::class, "updatePassword"]);
                });

                Route::prefix("api-credentials")->as("api-credentials.")->group(function () {
                    Route::get("/", [ApiCredentialController::class, "index"])->name("index");
                    Route::get("refresh", [ApiCredentialController::class, "refresh"])->name("refresh");
                    Route::post("update", [ApiCredentialController::class, "update"])->name("update");
                });
            });
        });
    });

    // Location
    Route::prefix("location")->group(function () {
        Route::get("states", [LocationController::class, "states"]);
        Route::get("cities", [LocationController::class, "cities"]);
        Route::get("lgas", [LocationController::class, "lgas"]);
        Route::get("towns", [LocationController::class, "towns"]);
    });
});
