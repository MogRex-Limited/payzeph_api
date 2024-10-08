<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId("user_id")->nullable()->constrained("users")->cascadeOnDelete();
            $table->foreignId("template_id")->nullable()->constrained("templates")->cascadeOnDelete();
            $table->string("type"); //SMS, Voice
            $table->string("uuid");
            $table->text("content");
            $table->string("sender")->nullable();
            $table->string("recipient");
            $table->string("provider")->nullable();
            $table->string("asset_url", 500)->nullable();
            $table->string("asset_caption", 500)->nullable();
            $table->string("status")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
