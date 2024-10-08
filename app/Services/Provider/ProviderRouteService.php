<?php

namespace App\Services\Provider;

use App\Exceptions\General\ModelNotFoundException;
use App\Models\ProviderRoute;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ProviderRouteService
{
    public static function getById($id): ProviderRoute
    {
        $provider_route = ProviderRoute::find($id);
        if (empty($provider_route)) {
            throw new ModelNotFoundException("Provider route not found");
        }
        return $provider_route;
    }

    public static function validate(array $data): array
    {
        $validator = Validator::make($data, [
            "provider_id" => "required|exists:providers,id",
            "name" => 'required|string',
            "description" => 'nullable|string',
            "is_default" => "nullable|numeric",
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
            $provider_route =  ProviderRoute::create($data);
            DB::commit();
            return $provider_route;
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
            $provider_route = self::getById($id);
            $provider_route->update($data);
            DB::commit();
            return $provider_route;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function list(array $data = [])
    {
        $builder = ProviderRoute::latest();

        if (!empty($provider_id = $data["provider_id"] ?? null)) {
            $builder = $builder->where("provider_id", $provider_id);
        }
 
        return $builder;
    }
}
