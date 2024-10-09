<?php

namespace App\Services\User;

use App\Constants\General\StatusConstants;
use App\Exceptions\General\InvalidRequestException;
use App\Exceptions\General\ModelNotFoundException;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class UserService
{
    public User $user;

    public static function init(): self
    {
        return app()->make(self::class);
    }

    public static function getById($key, $column = "id"): User
    {
        $model = User::where($column, $key)->first();
        if (empty($model)) {
            throw new ModelNotFoundException("User not found");
        }
        return $model;
    }


    public function validate(array $data, $id = null): array
    {
        $validator = Validator::make($data, [
            "avatar_id" => "nullable|numeric",
            "first_name" => "required|string",
            "middle_name" => "nullable|string",
            "last_name" => "nullable|string",
            "email" => "nullable|email|unique:users,email,$id|" . Rule::requiredIf(empty($id)),
            "status" => "nullable|string",
            "business_name" => "nullable|string",
            "business_category" => "nullable|string",
            'password' => [Rule::requiredIf(empty($id)), "string"],
            "phone_number" => "nullable|string",
        ], [
            'email.unique' => "The email address has already been used by another user",
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator->validated();
    }


    public function create(array $data): User
    {
        $data = self::validate($data);
        $data = array_merge([
            'status' => StatusConstants::ACTIVE,
            "zeph_id" => $this->generateZephCode($data["first_name"], $data["last_name"])
        ], $data);

        $data['password'] = Hash::make($data['password']);
        $user = User::create($data);
        return $user;
    }

    public function update(array $data, $id)
    {
        $data = $this->validate($data, $id);
        $user = $this->getById($id);
        $user->update($data);
        return $user;
    }

    public static function getNames($name)
    {
        $names = explode(' ', $name);
        list($first_name, $middle_name, $last_name) = array_pad($names, 3, null);

        return compact('first_name', 'middle_name', 'last_name');
    }

    function generateZephCode()
    {
        // Generate random numbers for the format ZXXXX-XXX (where X are random digits)
        $prefix = 'Z'; // Starting with "Z"
        $firstPart = str_pad(mt_rand(0, 9999), 4, '0', STR_PAD_LEFT); // Generate a 4-digit number, pad with zeros if necessary
        $secondPart = str_pad(mt_rand(0, 999), 3, '0', STR_PAD_LEFT); // Generate a 3-digit number, pad with zeros if necessary
    
        // Concatenate to form the code in ZXXXX-XXX format
        $uniqueCode = $prefix . $firstPart . '-' . $secondPart;
    
        // Check if the code already exists in the database
        $existing = User::where('zeph_id', $uniqueCode)->exists();
    
        // If it exists, regenerate a new code until a unique one is found
        while ($existing) {
            $firstPart = str_pad(mt_rand(0, 9999), 4, '0', STR_PAD_LEFT);
            $secondPart = str_pad(mt_rand(0, 999), 3, '0', STR_PAD_LEFT);
            $uniqueCode = $prefix . $firstPart . '-' . $secondPart;
            $existing = User::where('zeph_id', $uniqueCode)->exists();
        }
    
        // Return the unique code
        return $uniqueCode;
    }    

    public function updatePassword(array $data)
    {
        $validator = Validator::make($data, [
            'current_password' => "required|string",
            'password' => "required|string|confirmed"
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $data = $validator->validated();

        if(!Hash::check($data['current_password'], auth()->user()->password)){
            throw new InvalidRequestException("Password is incorrect");
        }

        $user = auth()->user();

        $user->update([
            "password" => Hash::make($data['password'])
        ]);

        return $user;
    }

}
