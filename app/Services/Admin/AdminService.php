<?php

namespace App\Services\Admin;

use App\Constants\General\StatusConstants;
use App\Exceptions\General\ModelNotFoundException;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AdminService
{
    public Admin $admin;

    public static function init(): self
    {
        return app()->make(self::class);
    }

    public static function getById($id): Admin
    {
        $model = Admin::where("id", $id)->first();
        if (empty($model)) {
            throw new ModelNotFoundException("Admin not found");
        }
        return $model;
    }


    public function validate(array $data, $id = null): array
    {
        $validator = Validator::make($data, [
            "avatar_id" => "nullable|numeric",
            "first_name" => "required|string",
            "middle_name" => "nullable|string",
            "last_name" => "required|string",
            "email" => "nullable|email|unique:admins,email,$id|" . Rule::requiredIf(empty($id)),
            "status" => "nullable|string",
            'password' => [Rule::requiredIf(empty($id)), "string"],
            "phone_number" => "nullable|string",
        ], [
            'email.unique' => "The email address has already been used by another admin",
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator->validated();
    }


    public function create(array $data): Admin
    {
        $data = self::validate($data);
        $data = array_merge([
            'status' => StatusConstants::ACTIVE,
        ], $data);
        $data['password'] = Hash::make($data['password']);
        $admin = Admin::create($data);
        return $admin;
    }

    public function update(array $data, $id)
    {
        $data = $this->validate($data, $id);

        $admin = $this->getById($id);
        $admin->update($data);

        return $admin;
    }
}
