<?php

namespace App\Services\User;

use App\Constants\General\StatusConstants;
use App\Constants\Media\FileConstants;
use App\Exceptions\General\ModelNotFoundException;
use App\Models\Avatar;
use App\Models\User;
use App\Services\Media\FileService;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AvatarService
{
    public ?User $user;
    public $file_service;
    public array $files = [];

    function __construct()
    {
        $this->user = auth()->user();
        $this->file_service = new FileService;
    }

    function setUser(User $user)
    {
        $this->user = $user;
        return $this;
    }

    public static function getById($id)
    {
        $avatar = Avatar::where("id", $id)->first();

        if (empty($avatar)) {
            throw new ModelNotFoundException("Avatar not found");
        }

        return $avatar;
    }

    public function validate(array $data): array
    {
        $validator = Validator::make($data, [
            "avatar_id" => "nullable|numeric|exists:avatars,id",
            "user_id" => "nullable|numeric|exists:users,id|" . Rule::requiredIf(empty($this->user)),
            "avatar_background_color" => "nullable|string"
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator->validated();
    }


    // public function save(array $data)
    // {
    //     DB::beginTransaction();
    //     try {
    //         $data = self::validate($data);
    //         if (empty($this->user)) {
    //             $this->user = User::with("avatar")->find($data["user_id"]);
    //         }
    //         $old_avatar = $this->user->avatar;

    //         $file = $data["avatar"];
    //         $this->files[] =  $new_avatar = $this->file_service->setFilename(
    //             $file->getClientOriginalName()
    //         )->saveFromFile(
    //             $file,
    //             FileConstants::AVATAR_PATH,
    //             null,
    //             auth()->id()
    //         );

    //         $this->user->update([
    //             "avatar_id" => $new_avatar->id
    //         ]);

    //         if (!empty($old_avatar)) {
    //             $old_avatar->cleanDelete();
    //         }

    //         DB::commit();
    //         return $new_avatar;
    //     } catch (\Exception $th) {
    //         foreach ($this->files as $file) {
    //             $file->cleanDelete(null, false);
    //         }
    //         DB::rollBack();
    //         throw $th;
    //     }
    // }

    public function save(array $data)
    {
        $data = self::validate($data);

        if (empty($this->user)) {
            $this->user = User::with("avatar")->find($data["user_id"]);
        }

        $this->user->update([
            "avatar_id" => $data["avatar_id"] ?? $this->user->avatar_id,
            "avatar_background_color" => $data["avatar_background_color"] ?? $this->user->avatar_background_color
        ]);
    }

    public function list()
    {
        return Avatar::latest();
    }

    public static function validateCrud(array $data, $id = null)
    {
        $validator = Validator::make($data, [
            "name" => 'required|string',
            "description" => 'nullable|string',
            "avatar" => "nullable|image|" . Rule::requiredIf(empty($id)),
            "status" => 'required|string|' . Rule::in(StatusConstants::ACTIVE_OPTIONS),
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator->validated();
    }

    public function create(array $data)
    {
        $data = self::validateCrud($data);


        if (!empty($image = $data["avatar"] ?? null)) {
            $data["image_id"] = $this->file_service->saveFromFile($image, FileConstants::AVATAR_PATH, null, auth()->id())->id;
            unset($data["avatar"]);
        }

        return Avatar::create($data);
    }

    public function update(array $data, $id)
    {
        $data = self::validateCrud($data, $id);

        $avatar = self::getById($id);

        if (!empty($image = $data["avatar"] ?? null)) {
            $data["image_id"] = $this->file_service->saveFromFile($image, FileConstants::AVATAR_PATH, $avatar->image_id, auth()->id())->id;
            unset($data["avatar"]);
        }

        return $avatar->update($data);
    }

    public function delete($avatar_id)
    {
        $avatar = self::getById($avatar_id);
        $avatar->delete();
        $this->file_service->cleanDelete($avatar->image_id);
    }
}
