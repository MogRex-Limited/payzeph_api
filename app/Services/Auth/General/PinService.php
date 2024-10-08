<?php

namespace App\Services\Auth\General;

use App\Constants\Auth\PinConstants;
use App\Exceptions\Auth\PinException;
use App\Helpers\MethodsHelper;
use App\Models\Pin;
use App\Services\Notification\AppMailerService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PinService
{

    public  function create(Model $model, array $data)
    {
        $validator = Validator::make($data, [
            "type" => "required|string|" . Rule::in(array_keys(PinConstants::TITLES)),
            "expires_at" => "required|date",
            "length" => "required|numeric",
            "code_type" => "required|in:int,string",
        ]);
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $data = $validator->validated();
        $query = ['type' => $data["type"]];
        if (!empty($model?->id)) {
            $query["model_class"] = $model::class;
            $query["model_id"] = $model->id;
            $query["email"] = $model->email;
        } else {
            $query["email"] = $model->email;
        }

        $pin = Pin::updateOrCreate($query, [
            'code' => MethodsHelper::generateRandomDigits($data["length"]),
            'expires_at' => $data["expires_at"],
        ]);

        AppMailerService::send([
            "data" => [
                "pin" => $pin,
                'model' => $model,
                "expires_at" => Carbon::parse($data["expires_at"])->diffForHumans()
            ],
            "to" => $model->email ?? $pin->email,
            "template" => "emails.auth.pin." . $data["type"],
            "subject" => PinConstants::TITLES[$data["type"]],
        ]);
    }


    public static function verify(array $data)
    {
        $validator = Validator::make($data, [
            "code" => "required|string",
        ]);
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
        $data = $validator->validated();

        $pin = Pin::where($data)->first();

        if (empty($pin)) {
            throw new PinException("The code is invalid. Kindly request a new code.");
        }

        if (!empty($ex = $pin->expires_at) && Carbon::parse($ex)->isPast()) {
            $pin->delete();
            throw new PinException("Code has expired, kindly request a new code");
        }

        $model = $pin->model;

        $pin->delete();

        return [
            "model" => $model,
            "pin" => $pin
        ];
    }
}
