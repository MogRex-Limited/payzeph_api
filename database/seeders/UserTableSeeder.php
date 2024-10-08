<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Services\Auth\Admin\AdminRegistrationService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (empty(Admin::where("email", config("system.emails.sudo"))->first())) {
            $admin = (new AdminRegistrationService)->create([
                "first_name" => "PayZeph",
                "last_name" => "Admin",
                "email" => config("system.emails.sudo"),
                "password" => "password",
            ]);
        }
    }
}
