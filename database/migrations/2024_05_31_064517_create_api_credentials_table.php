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
        Schema::create('api_credentials', function (Blueprint $table) {
            $table->id();
            $table->foreignId("user_id")->constrained("users")->cascadeOnDelete();
            $table->string('authentication_type')->nullable();
            $table->string('public_key', 500)->nullable();
            $table->string('private_key', 500)->nullable();
            $table->tinyInteger('connection_status')->default(0);
            $table->string('webhook_url', 500)->nullable();
            $table->string('callback_url', 500)->nullable();
            $table->dateTime('last_connection')->nullable();
            $table->string('status');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_credentials');
    }
};
