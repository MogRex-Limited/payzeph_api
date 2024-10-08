<?php

use App\Http\Controllers\Api\V1\User\Messaging\MessageController;
use App\Http\Controllers\Api\V1\User\Template\TemplateController;
use Illuminate\Support\Facades\Route;

Route::middleware(["auth:sanctum"])->group(function () {

    Route::resources([
        "templates" => TemplateController::class,
    ]);

    Route::prefix("messaging")->as("messaging.")->group(function () {
        Route::get("/", [MessageController::class, "index"])->name("index");
        Route::get("{id}/show", [MessageController::class, "show"])->name("show");
        Route::post("send", [MessageController::class, "send"])->name("send");
        Route::post("template/send", [MessageController::class, "sendViaTemplate"])->name("send");
    });
});
