<?php

namespace App\Services\Phonebook;

use App\Exceptions\General\ModelNotFoundException;
use App\Helpers\MethodsHelper;
use App\Models\PhonebookGroup;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class PhonebookGroupService
{
    public static function getById($key, $column = "id"): PhonebookGroup
    {
        $phonebook_group = PhonebookGroup::where($column, $key)->first();
        if (empty($phonebook_group)) {
            throw new ModelNotFoundException("Group not found");
        }
        return $phonebook_group;
    }

    public static function validate(array $data): array
    {
        $validator = Validator::make($data, [
            "name" => 'required|string',
            "description" => 'nullable|string',
            "status" => 'nullable|string',
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
            $data["user_id"] = auth()->id();
            $data["identifier"] = self::generateUniqueIdentifier();
            $phonebook_group =  PhonebookGroup::create($data);
            DB::commit();
            return $phonebook_group->refresh();
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
            $phonebook_group = self::getById($id);
            $phonebook_group->update($data);
            DB::commit();
            return $phonebook_group->refresh();
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public static function generateUniqueIdentifier($length = 5)
    {
        $key = "PBG_" . MethodsHelper::generateRandomDigits($length);
        $check = PhonebookGroup::where("identifier", $key)->count();
        if ($check > 0) {
            return self::generateUniqueIdentifier();
        }
        return $key;
    }

    public function list()
    {
        return PhonebookGroup::latest();
    }
}
