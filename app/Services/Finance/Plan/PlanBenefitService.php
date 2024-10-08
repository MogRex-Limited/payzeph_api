<?php

namespace App\Services\Finance\Plan;

use App\Constants\Finance\PlanConstants;
use App\Constants\General\StatusConstants;
use App\Exceptions\General\ModelNotFoundException;
use App\Models\PlanBenefit;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PlanBenefitService
{

    public static function getById($id): PlanBenefit
    {
        $plan_benefit = PlanBenefit::find($id);
        if (empty($plan_benefit)) {
            throw new ModelNotFoundException("Benefit not found");
        }
        return $plan_benefit;
    }

    public static function validate(array $data, $id = null): array
    {
        $newData = [
            "value" => array_intersect_key($data['value'], array_flip($data['key']))
        ];

        $data = array_merge($data, $newData);
        $validator = Validator::make($data, [
            "status" => "nullable|string|" . Rule::in(StatusConstants::ACTIVE_OPTIONS),
            "key" => 'required|array',
            "key.*" => Rule::in(array_keys(PlanConstants::BENEFIT_OPTIONS)),
            "value" => 'required|array',
            "value.*" => 'required|string',
            "duration" => 'nullable|numeric|gt:-1',
            "plan_id" => "required|string|exists:plans,id",
            "action" => "required|string"
        ], [
            'value.*.required' => "The :attribute field is required.",
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator->validated();
    }

    public function save(array $data, $id = null)
    {
        $data = $this->validate($data, $id);
        $plan = PlanService::getById($data["plan_id"]);

        switch ($data["action"]) {
            case 'save':
                foreach ($data['value'] as $key => $value) {
                    PlanBenefit::updateOrCreate([
                        'plan_id' => $data['plan_id'],
                        "key" => $key,
                    ], [
                        "title" => PlanConstants::BENEFIT_OPTIONS[$key],
                        "value" => $data["value"][$key],
                        "value_type" => "Fixed",
                        "duration" => $data["duration"] ?? null,
                        "description" => PlanConstants::BENEFIT_DESCRIPTIONS[$key] ?? null,
                        "status" => StatusConstants::ACTIVE
                    ]);
                }

                $plan = $plan->refresh();
                return [
                    "plan" => $plan,
                    "message" => 'Benefits updated successfully'
                ];
            case 'reset':
                PlanBenefit::where([
                    'plan_id' => $data['plan_id'],
                ])->delete();

                return [
                    "plan" => $plan,
                    "message" => "Reset successful"
                ];
        }
    }
}
