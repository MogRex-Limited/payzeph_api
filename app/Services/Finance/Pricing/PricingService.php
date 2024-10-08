<?php

namespace App\Services\Finance\Pricing;

use App\Exceptions\General\ModelNotFoundException;
use App\Models\PricingLevel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class PricingService
{
    public static function getById($id): PricingLevel
    {
        $pricing_level = PricingLevel::find($id);
        if (empty($pricing_level)) {
            throw new ModelNotFoundException("Pricing level not found");
        }
        return $pricing_level;
    }

    public static function validate(array $data): array
    {
        $validator = Validator::make($data, [
            "user_id" => "nullable|exists:users,id",
            "minimum" => 'required|numeric',
            "maximum" => 'required|numeric',
            "amount" => 'required|numeric',
            "status" => 'required|string',
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
            $pricing_level =  PricingLevel::create($data);
            DB::commit();
            return $pricing_level;
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
            $pricing_level = self::getById($id);
            $pricing_level->update($data);

            DB::commit();
            return $pricing_level;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public static function calculate(array $data)
    {
        $validator = Validator::make($data, [
            "quantity" => 'required|numeric|gt:-1',
            "type" => "required|string|in:unit,money"
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $validatedData = $validator->validated();

        $response = null;

        if ($validatedData["type"] == "unit") {
            $pricingLevels = self::list()->status()->where("minimum", "<", $validatedData["quantity"])->get();

            if ($pricingLevels->isEmpty()) {
                return null;
            }

            $pricingLevelValue = self::getPricingLevelValue($validatedData["quantity"]);

            $response = [
                "type" => $validatedData["type"],
                "unit" => $validatedData["quantity"],
                "price" => !is_null($pricingLevelValue) ? ($pricingLevelValue * $validatedData["quantity"]) : 0,
            ];
        } elseif ($validatedData["type"] == "money") {
            $pricingLevels = self::list()->status()->get();

            $requiredPricingLevel = null;

            foreach ($pricingLevels as $pricingLevel) {
                $maximumPrice = $pricingLevel->maximum * $pricingLevel->amount;
                $minimumPrice = $pricingLevel->minimum * $pricingLevel->amount;

                if (in_array($validatedData["quantity"], range($minimumPrice, $maximumPrice))) {
                    $requiredPricingLevel = $pricingLevel;
                    break;
                }
            }

            if (!$requiredPricingLevel) {
                return null;
            }

            $response = [
                "type" => $validatedData["type"],
                "price" => $validatedData["quantity"],
                "unit" => !is_null($requiredPricingLevel) ? ($validatedData["quantity"] / $requiredPricingLevel->amount) : 0,
            ];
        }

        return $response;
    }

    public static function getPricingLevelValue($value)
    {
        if (is_null($value)) {
            return null;
        }

        $pricing_level = PricingLevel::status()
            ->whereRaw('? BETWEEN minimum AND maximum', [$value])->first();

        return $pricing_level->amount ?? null;
    }

    public static function list()
    {
        return PricingLevel::latest();
    }
}
