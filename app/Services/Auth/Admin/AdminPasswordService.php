<?php

namespace App\Services\Auth\Admin;

use App\Constants\Auth\PinConstants;
use App\Models\Admin;
use App\Services\Auth\General\PinService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AdminPasswordService
{
    public $pin_service;
    function __construct()
    {
        $this->pin_service = new PinService;
    }

    public function sendPasswordResetPin(array $data)
    {
        $validator = Validator::make($data,[
            'email' => 'required|email|exists:admins,email',
        ], [
            'email.exists' => "The email address does not exist in our records.",
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
        $data = $validator->validated();
        $admin = Admin::where($data)->first();
        $pin_expiry = now()->addSeconds(config("system.configuration.pin_expiry"));

        $this->pin_service->create($admin, [
            "type" => PinConstants::TYPE_PASSWORD_RESET,
            "expires_at" => $pin_expiry,
            "length" => 4,
            "code_type" => "int",
        ]);
    }


    public function resetPassword(array $data)
    {
        $validator = Validator::make($data, [
            "code" => "required|string",
            'password' => ['required', 'string', 'confirmed'],
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $data = $validator->validated();

        $check = $this->pin_service->verify([
            "code" => $data["code"],
            "type" => PinConstants::TYPE_PASSWORD_RESET
        ]);

        $admin = $check["model"];
        
        $admin->update([
            "password" => Hash::make($data["password"])
        ]);
    }
}
