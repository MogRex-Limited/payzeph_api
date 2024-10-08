<?php

use App\Http\Controllers\Api\V1\Admin\Finance\Payment\PaymentController;
use App\Http\Controllers\Api\V1\Admin\Finance\Plan\PlanBenefitController;
use App\Http\Controllers\Api\V1\Admin\Finance\Plan\PlanController;
use App\Http\Controllers\Api\V1\Admin\Finance\Pricing\PricingController;
use App\Http\Controllers\Api\V1\Admin\Profile\ProfileController;
use App\Http\Controllers\Api\V1\Admin\Provider\ProviderController;
use App\Http\Controllers\Api\V1\Admin\Provider\ProviderRouteController;
use App\Http\Controllers\Api\V1\Admin\SenderIdentifier\SenderIdentifierController;
use App\Http\Controllers\Api\V1\Admin\User\UserController;
use App\Http\Controllers\Api\V1\Auth\Admin\AdminLoginController;
use App\Http\Controllers\Api\V1\Auth\Admin\AdminPasswordController;
use App\Http\Controllers\Api\V1\Auth\Admin\AdminRegisterController;
use App\Http\Controllers\Api\V1\Auth\Admin\AdminVerificationController;
use App\Http\Controllers\Api\V1\General\GeneralController;
use Illuminate\Support\Facades\Route;

Route::middleware(["apiKey"])->group(function () {
    Route::post("account/preview", [GeneralController::class, "previewAdmin"])->name("preview-admin");

    Route::prefix("auth")->as("auth.")->group(function () {
        Route::post("/register", [AdminRegisterController::class, "register"])->name("register");
        Route::post("/login", [AdminLoginController::class, "login"])->name("login");
        Route::post("/logout", [AdminLoginController::class, "logout"])->name("logout")->middleware(["auth:sanctum"]);
        Route::post("/login/preview", [AdminLoginController::class, "loginPreview"])->name("login_preview");

        Route::prefix("password")->as("password.")->group(function () {
            Route::post('/forgot', [AdminPasswordController::class, 'forgotPassword'])->name("forgot_password");
            Route::post("/reset", [AdminPasswordController::class,  "resetPassword"])->name("reset_password");
        });

        Route::middleware(["auth:sanctum"])->prefix("otp")->as("otp.")->group(function () {
            Route::post('/request', [AdminVerificationController::class, 'request'])->name("request");
            Route::post("/verify", [AdminVerificationController::class,  "verify"])->name("verify");
        });
    });

    Route::middleware("auth:sanctum")->group(function () {
        Route::apiResources([
            "users" => UserController::class,
            "pricing-levels" => PricingController::class,
            "providers" => ProviderController::class,
            "provider-routes" => ProviderRouteController::class,
            "sender-identifiers" => SenderIdentifierController::class
        ]);

        Route::prefix("profile")->as("profile.")->group(function () {
            Route::get("show", [ProfileController::class, "show"]);
            Route::post("update", [ProfileController::class, "update"]);
        });

        Route::prefix("sender-identifiers")->as("sender-identifiers.")->group(function () {
            Route::post("{id}/send-to-provider", [SenderIdentifierController::class, "sendToProvider"])->name("send-to-provider");
        });

        Route::prefix("billings")->group(function () {
            Route::prefix("plans")->group(function () {
                Route::get("/", [PlanController::class, "index"]);
                Route::post("create", [PlanController::class, "create"]);
                Route::get("show/{plan}", [PlanController::class, "show"]);
                Route::post("update/{plan}", [PlanController::class, "update"]);
                Route::delete("destroy/{plan}", [PlanController::class, "destroy"]);

                Route::prefix("{plan}/benefits")->group(function () {
                    Route::get("/", [PlanBenefitController::class, "index"]);
                    Route::get("show/{benefit}", [PlanBenefitController::class, "show"]);
                    Route::post("save", [PlanBenefitController::class, "create"]);
                });
            });
        });

        Route::prefix("payments")->as("payments.")->group(function () {
            Route::post("initiate", [PaymentController::class, "initiate"])->name("initiate");
        });
    });
});
