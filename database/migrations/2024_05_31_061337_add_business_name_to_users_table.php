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
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumns("users", ["business_name", "business_category"])) {
                $table->string("business_name")->nullable()->after("phone_number");
                $table->string("business_category")->nullable()->after("business_name");
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumns("users", ["business_name", "business_category"])) {
                $table->dropColumn("business_name");
                $table->dropColumn("business_category");
            }
        });
    }
};
