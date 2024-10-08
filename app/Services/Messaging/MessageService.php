<?php

namespace App\Services\Messaging;

use App\Constants\General\StatusConstants;
use App\Constants\Messaging\MessagingConstants;
use App\Exceptions\General\InvalidRequestException;
use App\Exceptions\General\ModelNotFoundException;
use App\Helpers\MethodsHelper;
use App\Models\Message;
use App\Models\SenderIdentifier;
use App\Services\Messaging\Provider\TermiiMessagingService;
use App\Services\SenderIdentifier\SenderIdentifierService;
use App\Services\Template\TemplateService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class MessageService extends BaseMessageService
{
    public $payload;
    public $sender;

    public function __construct($user)
    {
        parent::__construct($user);
    }

    public static function getById($id, $column = "id"): Message
    {
        $template = Message::where($column, $id)->first();
        if (empty($template)) {
            throw new ModelNotFoundException("Message not found");
        }
        return $template;
    }

    public function send(array $data)
    {
        $this->validate($data);
        $this->verifySender();

        $message = $this->create([
            "user_id" => $this->user->id,
            "template_id" => $this->validated_payload["template_id"] ?? null,
            "type" => $this->validated_payload["type"],
            "sender" => $this->sender->identifier,
            "content" => $this->validated_payload["content"],
            "provider" => $this->provider,
            "recipient" => $this->validated_payload["recipient"],
            "status" => StatusConstants::PENDING
        ]);

        $this->payload = [
            "user" => $this->user,
            "base_payload" => $this->validated_payload,
            "sender" => $this->sender,
            "message" => $message
        ];

        if ($this->provider == MessagingConstants::TERMII) {
            $handler = new TermiiMessagingService($this->user);
        }

        $handler->init($this->payload);
        return $handler->message;
    }

    public function verifySender()
    {
        $key = $this->validated_payload["identifier"] ?? null;

        $sender = $key
            ? (new SenderIdentifierService())->getById($key, "identifier")
            : SenderIdentifier::where("user_id", $this->user->id)
            ->where("is_default", 1)
            ->orWhere("user_id", $this->user->id)
            ->first();

        if (!$sender) {
            throw new InvalidRequestException("You do not have a valid sender ID. Please create one and try again.");
        }

        if ($sender->status !== StatusConstants::APPROVED) {
            throw new InvalidRequestException("Your sender ID has not been approved. Please try another one or contact support.");
        }

        $this->sender = $sender;
        return $sender;
    }

    public function sendViaTemplate(array $data)
    {
        $this->validate($data);
        $this->verifySender();

        $template = (new TemplateService)->getById($this->validated_payload["template_id"], "uuid");
        $message = $this->composeMessage($template, $this->validated_payload["data"]);

        $message = $this->create([
            "user_id" => $this->user->id,
            "template_id" => $template?->id ?? null,
            "type" => $this->validated_payload["type"],
            "sender" => $this->sender->identifier,
            "content" => $message,
            "provider" => $this->provider,
            "recipient" => $this->validated_payload["recipient"],
            "status" => StatusConstants::PENDING
        ]);

        $this->payload = [
            "user" => $this->user,
            "base_payload" => $this->validated_payload,
            "sender" => $this->sender,
            "message" => $message
        ];

        if ($this->provider == MessagingConstants::TERMII) {
            $handler = new TermiiMessagingService($this->user);
        }

        $handler->init($this->payload);
        return $handler->message;
    }

    public function create(array $data)
    {
        DB::beginTransaction();
        try {
            $validator = Validator::make($data, [
                "user_id" => "nullable|exists:users,id",
                "template_id" => "nullable|exists:templates,id",
                "type" => "required|string",
                "sender" => "required|string",
                "content" => "required|string",
                "provider" => "required|string",
                "recipient" => "required|string",
                "status" => "required|string",
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            $data = $validator->validated();

            $message = Message::create([
                "user_id" => $data["user_id"] ?? $this->user->id,
                "uuid" => self::generateUUID(),
                ...$data
            ]);

            DB::commit();
            return $message;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public static function generateUUID($length = 8)
    {
        $key = "MID" . MethodsHelper::getRandomToken($length, true);
        $check = Message::where("uuid", $key)->count();
        if ($check > 0) {
            return self::generateUUID();
        }
        return $key;
    }

    public function composeMessage($template, $payload)
    {
        $message = $template->content;

        foreach ($payload as $key => $value) {
            $placeholder = "{{{$key}}}";
            if (strpos($message, $placeholder) !== false) {
                $message = str_replace($placeholder, $value, $message);
            }
        }

        return $message;
    }

    public function list(array $data = [])
    {
        return Message::latest()->where("user_id", $this->user->id);
    }
}
