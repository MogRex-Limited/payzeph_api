<?php

namespace App\Services\Template;

use App\Constants\General\StatusConstants;
use App\Exceptions\General\ModelNotFoundException;
use App\Helpers\MethodsHelper;
use App\Models\Template;
use App\Services\External\TermiiService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class TemplateService
{
    public $termii_service;

    public function __construct()
    {
        $this->termii_service = new TermiiService;
    }

    public static function getById($key, $column = "id"): Template
    {
        $template = Template::where($column, $key)->first();
        if (empty($template)) {
            throw new ModelNotFoundException("Template not found");
        }
        return $template;
    }

    public static function validate(array $data): array
    {
        $validator = Validator::make($data, [
            "user_id" => "nullable|exists:users,id",
            "name" => 'required|string',
            "content" => 'required|string',
            "type" => "required|string",
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

            $template =  Template::create([
                "user_id" => $data["user_id"] ?? auth()->id(),
                "uuid" => self::generateUUID(),
                "status" => StatusConstants::APPROVED,
                ...$data
            ]);

            DB::commit();
            return $template->refresh();
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
            $template = self::getById($id, "uuid");
            $template->update($data);
            DB::commit();
            return $template->refresh();
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function list()
    {
        return Template::latest();
    }

    public static function generateUUID($length = 8)
    {
        $key = "LBT" . MethodsHelper::getRandomToken($length, true);
        $check = Template::where("uuid", $key)->count();
        if ($check > 0) {
            return self::generateUUID();
        }
        return $key;
    }
}
