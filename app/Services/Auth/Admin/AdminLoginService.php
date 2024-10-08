<?php

namespace App\Services\Auth\Admin;

use App\Exceptions\Auth\AuthException;
use App\Models\Admin;
use App\Services\Auth\Admin\AdminRegistrationService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AdminLoginService
{
    public static function preview($data)
    {
        $validator = Validator::make($data, [
            'email' => 'required|email|exists:admins,email',
        ], [
            'email.exists' => "The email address does not exist in our records.",
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
        $data = $validator->validated();

        $admin = Admin::where('email', $data["email"])->first();
        return $admin;
    }

    public static function authenticate($data)
    {
        $validator = Validator::make($data, [
            'password' => ['required', 'string'],
            'email' => 'required|email|exists:admins,email',
        ], [
            'email.exists' => "The email address does not exist in our records.",
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
        $data = $validator->validated();

        $admin = Admin::where('email', $data["email"])->first();

        if (!Hash::check($data["password"], $admin->password)) {
            throw new AuthException("Incorrect password provided.");
        }

        return $admin;
    }

    public static function newLogin(Admin $admin)
    {
        //Todo: Log the admin activity
        (new AdminRegistrationService)->postRegisterActions($admin);
    }
}
