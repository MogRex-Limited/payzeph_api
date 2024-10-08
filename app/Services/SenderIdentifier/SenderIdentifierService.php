<?php

namespace App\Services\SenderIdentifier;

use App\Constants\General\StatusConstants;
use App\Exceptions\General\InvalidRequestException;
use App\Exceptions\General\ModelNotFoundException;
use App\Models\SenderIdentifier;
use App\Services\External\TermiiService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class SenderIdentifierService
{
    public $termii_service;

    public function __construct()
    {
        $this->termii_service = new TermiiService;
    }

    public static function getById($key, $column = "id"): SenderIdentifier
    {
        $sender_identifier = SenderIdentifier::where($column, $key)->first();
        if (empty($sender_identifier)) {
            throw new ModelNotFoundException("Sender ID not found");
        }
        return $sender_identifier;
    }

    public static function validate(array $data): array
    {
        $validator = Validator::make($data, [
            "user_id" => "required|exists:users,id",
            "identifier" => 'required|string',
            "is_default" => "nullable|numeric",
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator->validated();
    }

    public static function create(array $data)
    {
        DB::beginTransaction();
        try {
            $data = self::validate($data);
            $sender_identifier =  SenderIdentifier::create($data);
            DB::commit();
            return $sender_identifier->refresh();
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public static function update(array $data, $id)
    {
        DB::beginTransaction();
        try {
            $data = self::validate($data);

            $sender_identifier = self::getById($id);

            if (in_array($sender_identifier->status, [StatusConstants::APPROVED])) {
                throw new InvalidRequestException("This identifier can no longer be updated because it has been approved.");
            }

            $sender_identifier->update($data);
            DB::commit();
            return $sender_identifier->refresh();
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public static function sendToProvider($id)
    {
        DB::beginTransaction();
        try {
            $sender_identifier = self::getById($id);

            if (in_array($sender_identifier->status, [StatusConstants::APPROVED])) {
                throw new InvalidRequestException("This identifier can no longer be updated because it has been approved.");
            }

            (new TermiiService)->senderIdData([
                "sender_id" => $sender_identifier->identifier,
                "company" => $sender_identifier->user->business_name ?? $sender_identifier->user->full_name,
                "usecase" => "Welcome to LeadBulkSMS, the prime SMS solution for your businesses.",
            ])->createSenderId();

            $sender_identifier->update([
                "status" =>  StatusConstants::PROCESSING
            ]);

            DB::commit();
            return $sender_identifier->refresh();
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function list()
    {
        return SenderIdentifier::latest();
    }
}
