<?php

namespace App\Services\Setting;

use App\Constants\General\StatusConstants;
use App\Exceptions\General\InvalidRequestException;
use App\Exceptions\General\ModelNotFoundException;
use App\Helpers\MethodsHelper;
use App\Models\ApiCredential;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

class ApiCredentialService
{
    const PUBLIC = "Public";
    const PRIVATE = "Private";

    public $private_key = null;
    public $public_key = null;

    public static function getById($id): ApiCredential
    {
        $api_credential = ApiCredential::find($id);
        if (empty($api_credential)) {
            throw new ModelNotFoundException("API Credentials Not Found");
        }
        return $api_credential;
    }

    public static function getByUserId($user_id): ApiCredential
    {
        $api_credential = ApiCredential::where("user_id", $user_id)->first();
        if (empty($api_credential)) {
            throw new ModelNotFoundException("API Credentials Not Found");
        }
        return $api_credential;
    }
    public function generateKeys()
    {
        $this->public_key = $this->generatePublicKey();
        $this->private_key = $this->generateSecretKey();
        return $this;
    }

    public static function generatePublicKey()
    {
        $code = "pk_" . bin2hex(MethodsHelper::getRandomToken(30));
        if (ApiCredential::where("public_key", $code)->withTrashed()->count() > 0) {
            return self::generatePublicKey();
        }
        return $code;
    }

    public static function generateSecretKey()
    {
        $code = "sk_" . bin2hex(MethodsHelper::getRandomToken(30));
        if (ApiCredential::where("private_key", $code)->withTrashed()->count() > 0) {
            return self::generateSecretKey();
        }
        return $code;
    }

    public function validate(array $data, $id = null)
    {
        $validator = Validator::make($data, [
            "user_id" => "nullable|exists:users,id|" . Rule::requiredIf(empty($id)),
            "webhook_url" => "nullable|string",
            "callback_url" => "nullable|string",
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator->validated();
    }

    public function save(array $data)
    {
        $data = self::validate($data);
        return ApiCredential::create(array_merge($data, [
            "public_key" => $this->public_key,
            "private_key" => $this->private_key,
            "status" => StatusConstants::ACTIVE,
        ]));
    }

    public function refresh($id)
    {
        $api_credential = self::getById($id);
        $api_credential->update([
            "public_key" => $this->public_key,
            "private_key" => $this->private_key,
        ]);

        return $api_credential->refresh();
    }

    public function update(array $data, $id)
    {
        $data = self::validate($data, $id);
        $api_credential = self::getById($id);
        $api_credential->update($data);
        return $api_credential->refresh();
    }

    public function authenticate($private_key)
    {
        try {
            $credential = ApiCredential::where("private_key", $private_key)->first();

            if (empty($credential)) {
                throw new InvalidRequestException("Invalid Authorization Key");
            }

            $credential->update([
                "connection_status" => 1,
                "last_connection" => now(),
            ]);

            return true;
        } catch (Throwable $th) {
            throw $th;
        }
    }
}
