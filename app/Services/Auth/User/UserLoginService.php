<?php

namespace App\Services\Auth\User;

use App\Exceptions\Auth\AuthException;
use App\Models\User;
use App\Services\Auth\User\UserRegistrationService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class UserLoginService
{
    public static function preview($data)
    {
        $validator = Validator::make($data, [
            'email' => 'required|email|exists:users,email',
        ], [
            'email.exists' => "The email address does not exist in our records.",
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
        $data = $validator->validated();

        $user = User::where('email', $data["email"])->first();
        return $user;
    }

    public static function authenticate($data)
    {
        $validator = Validator::make($data, [
            'password' => ['required', 'string'],
            'email' => 'required|email|exists:users,email',
        ], [
            'email.exists' => "The email address does not exist in our records.",
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
        $data = $validator->validated();

        $user = User::where('email', $data["email"])->first();

        if (!Hash::check($data["password"], $user->password)) {
            throw new AuthException("Incorrect password provided.");
        }

        return $user;
    }

    public static function newLogin(User $user)
    {
        //Todo: Log the user activity
        (new UserRegistrationService)->postRegisterActions($user);
    }
}
