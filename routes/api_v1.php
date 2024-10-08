<?php

use App\Http\Controllers\Api\V1\Auth\User\UserLoginController;
use App\Http\Controllers\Api\V1\Auth\User\UserPasswordController;
use App\Http\Controllers\Api\V1\Auth\User\UserRegisterController;
use App\Http\Controllers\Api\V1\Auth\User\UserVerificationController;
use App\Http\Controllers\Api\V1\General\GeneralController;
use App\Http\Controllers\Api\V1\Location\LocationController;
use App\Http\Controllers\Api\V1\User\Finance\Plan\PlanController;
use App\Http\Controllers\Api\V1\User\Finance\Pricing\PricingController;
use App\Http\Controllers\Api\V1\User\Finance\Payment\PaymentController;
use App\Http\Controllers\Api\V1\User\Finance\Subscription\SubscriptionController;
use App\Http\Controllers\Api\V1\User\Finance\Transaction\TransactionController;
use App\Http\Controllers\Api\V1\User\Messaging\MessageController;
use App\Http\Controllers\Api\V1\User\Phonebook\PhonebookController;
use App\Http\Controllers\Api\V1\User\Phonebook\PhonebookGroupController;
use App\Http\Controllers\Webhook\SquadWehbookHandlingController;
use App\Http\Controllers\Api\V1\User\SenderIdentifier\SenderIdentifierController;
use App\Http\Controllers\Api\V1\User\Setting\ApiCredentialController;
use App\Http\Controllers\Api\V1\User\Setting\ProfileController;
use App\Http\Controllers\Api\V1\User\Template\TemplateController;
use Illuminate\Support\Facades\Route;

Route::post('/squad/webhook/verifications', [SquadWehbookHandlingController::class, 'handleWebhook'])->name('handle-squad-webhook');

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

        Route::get("pricing", [PricingController::class, "index"])->name("pricing.index");
        Route::get("pricing/{id}/show", [PricingController::class, "show"])->name("pricing.show");
        Route::get("pricing/calculate", [PricingController::class, "calculate"])->name("pricing.calculate");

        Route::middleware("auth:sanctum")->group(function () {
            Route::prefix("transactions")->as("transactions.")->group(function () {
                Route::get("/", [TransactionController::class, "index"]);
                Route::get("{transaction}/show", [TransactionController::class, "show"]);
            });

            Route::apiResources([
                "phonebook-groups" => PhonebookGroupController::class,
                "phonebook" => PhonebookController::class,
                "sender-identifiers" => SenderIdentifierController::class,
                "templates" => TemplateController::class,
            ]);

            Route::prefix("phonebook")->as("phonebook.")->group(function () {
                Route::post("delete-items", [PhonebookController::class, "deleteItems"])->name("delete-items");
                Route::post("import", [PhonebookController::class, "importCsv"])->name("import");
                Route::post("enter-contact", [PhonebookController::class, "writeUpload"])->name("write-upload");
            });

            Route::prefix("billings")->group(function () {
                Route::get("plans", [PlanController::class, "index"]);
                Route::get("plans/show/{plan}", [PlanController::class, "show"]);

                Route::prefix("subscriptions")->group(function () {
                    Route::get("/", [SubscriptionController::class, "index"]);
                    Route::get("show/{subscription}", [SubscriptionController::class, "show"]);
                    Route::post("initiate", [SubscriptionController::class, "subscribe"]);
                });
            });

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

            Route::prefix("messaging")->as("messaging.")->group(function () {
                Route::get("/", [MessageController::class, "index"])->name("index");
                Route::get("{id}/show", [MessageController::class, "show"])->name("show");
                Route::post("send", [MessageController::class, "send"])->name("send");
                Route::post("template/send", [MessageController::class, "sendViaTemplate"])->name("send");
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
