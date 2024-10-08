<?php

namespace App\Services\Auth\Admin;

use App\Constants\Auth\PinConstants;
use App\Exceptions\Auth\PinException;
use App\Services\Auth\General\PinService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AdminVerifyService
{
    public $pin_service;
    function __construct()
    {
        $this->pin_service = new PinService;
    }

    public function sendPin($admin, $type = PinConstants::TYPE_VERIFY_EMAIL)
    {
        $pin_expiry = now()->addSeconds(config("system.configuration.pin_expiry"));
        $this->pin_service->create($admin, [
            "type" => $type,
            "expires_at" => $pin_expiry,
            "length" => 4,
            "code_type" => "int",
        ]);
    }


    public function verify(array $data)
    {
        DB::beginTransaction();
        try {
            $validator = Validator::make($data, [
                "code" => "required|string",
                "email" => [auth("admin")->check() ? "nullable" : "required", "email"],
                "type" => "required|string"
            ]);
            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            $data = $validator->validated();
            $check = $this->pin_service->verify($data);
            $pin = $check["pin"];

            if (!empty($admin = $check["model"])) {
                if ($admin->id != auth("admin")->id()) {
                    throw new PinException("The code is invalid. Kindly request a new code.");
                }
            } else {
                if ($pin->email != $data["email"]) {
                    throw new PinException("The email address does not match the code. Kindly request a new code.");
                }
            }
            DB::commit();
        } catch (\Exception $th) {
            DB::rollBack();
            throw $th;
        }
    }
}
