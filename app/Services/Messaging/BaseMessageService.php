<?php

namespace App\Services\Messaging;

use App\Constants\Messaging\MessagingConstants;
use App\Exceptions\General\InvalidRequestException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class BaseMessageService
{
    public $user;
    public $provider;
    public $validated_payload;

    public function __construct($user)
    {
        $this->user = $user;
    }

    public function setProvider(?string $provider = null)
    {
        $selected_provider = $provider ?? globalSetting()?->message_provider;

        if (empty($selected_provider)) {
            $selected_provider = MessagingConstants::TERMII;
        }

        if (!in_array($selected_provider, [MessagingConstants::TERMII])) {
            throw new InvalidRequestException("Invalid provider selected");
        }

        $this->provider = $selected_provider;
        return $this;
    }

    public function validate(array $data)
    {
        $validator = Validator::make($data, [
            "type" => "required|string",
            "recipient" => "required|string",
            "content" => 'nullable|string|' . Rule::requiredIf(empty($data["template_id"] ?? null)),
            "identifier" => "nullable|string|exists:sender_identifiers,identifier",
            "template_id" => "nullable|exists:templates,uuid",
            "data" => "nullable|array|" . Rule::requiredIf(!empty($data["template_id"] ?? null)),
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $validated_data = $validator->validated();
        $this->validated_payload = $validated_data;

        return $this;
    }
}
