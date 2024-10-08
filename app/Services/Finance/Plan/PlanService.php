<?php

namespace App\Services\Finance\Plan;

use App\Constants\Account\User\UserConstants;
use App\Constants\Finance\PlanConstants;
use App\Constants\General\StatusConstants;
use App\Constants\Media\FileConstants;
use App\Exceptions\Finance\Plan\PlanException;
use App\Exceptions\General\ModelNotFoundException;
use App\Helpers\MethodsHelper;
use App\Models\Plan;
use App\Services\Media\FileService;
use App\Services\User\UserService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PlanService
{
    public static function getById($id): Plan
    {
        $plan = Plan::find($id);
        if (empty($plan)) {
            throw new ModelNotFoundException("Plan not found");
        }
        return $plan;
    }

    public static function validate(array $data, $id = null): array
    {
        $validator = Validator::make($data, [
            "name" => 'required|string',
            "description" => 'nullable|string',
            "currency_id" => 'required|exists:currencies,id',
            "user_id" => "nullable|exists:users,uuid",
            "price" => 'required|numeric|gt:-1',
            "logo" => "nullable|image",
            "type" => 'nullable|string',
            "target" => 'nullable|string|' . Rule::in(UserConstants::ADMIN_TYPES),
            "frequency" => 'required|string|' . Rule::in(PlanConstants::FREQUENCY_OPTIONS),
            "status" => "required|string|" . Rule::in(StatusConstants::ACTIVE_OPTIONS),
        ]);

        $validator->sometimes(['parish_id'], 'required', function ($input) {
            return ($input->type == PlanConstants::CUSTOM);
        });

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

            if (!empty($logo = $data["logo"] ?? null)) {
                $fileService = new FileService;
                $data["logo_id"] = $fileService->saveFromFile($logo, FileConstants::PLAN_FILE_PATH, null, auth()->id())->id;
                unset($data["logo"]);
            }

            $data["duration"] = MethodsHelper::frequencyDuration($data["frequency"]);

            if (!empty($user_id = $data["user_id"] ?? null)) {
                $data["user_id"] = (new UserService)->getById($user_id, "uuid")->id;
            }

            $plan =  Plan::create($data);

            DB::commit();
            return $plan;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public static function update(array $data, $id)
    {
        DB::beginTransaction();
        try {
            $data = self::validate($data, $id);
            $plan = self::getById($id);

            if (!empty($logo = $data["logo"] ?? null)) {
                $fileService = new FileService;
                $data["logo_id"] = $fileService->saveFromFile($logo, FileConstants::PLAN_FILE_PATH, $plan->logo_id, auth()->id())->id;
                unset($data["logo"]);
            }

            $data["duration"] = MethodsHelper::frequencyDuration($data["frequency"]);

            if ($data["type"] == PlanConstants::STATIC) {
                $data["parish_id"] = null;
            }

            $plan->update($data);

            DB::commit();
            return $plan;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function list()
    {
        return Plan::with("benefits");
    }
}
