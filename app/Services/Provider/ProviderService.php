<?php

namespace App\Services\Provider;

use App\Exceptions\General\ModelNotFoundException;
use App\Models\Provider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ProviderService
{
    public static function getById($id): Provider
    {
        $provider = Provider::find($id);
        if (empty($provider)) {
            throw new ModelNotFoundException("Provider not found");
        }
        return $provider;
    }

    public static function validate(array $data): array
    {
        $validator = Validator::make($data, [
            "name" => 'required|string',
            "description" => 'nullable|string',
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
            $provider =  Provider::create($data);
            DB::commit();
            return $provider;
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
            $provider = self::getById($id);
            $provider->update($data);

            DB::commit();
            return $provider;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function list()
    {
        return Provider::latest();
    }
}
