<?php

namespace App\Services\Phonebook;

use App\Constants\Media\FileConstants;
use App\Exceptions\General\ModelNotFoundException;
use App\Imports\Phonebook\PhonebookImport;
use App\Models\Phonebook;
use App\Services\Media\FileService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;

class PhonebookService
{
    protected $file_service;
    protected $import_file;
    public function __construct()
    {
        $this->file_service = new FileService();
    }

    public static function getById($id): Phonebook
    {
        $phonebook = Phonebook::find($id);
        if (empty($phonebook)) {
            throw new ModelNotFoundException("Phonebook not found");
        }
        return $phonebook;
    }

    public static function validate(array $data): array
    {
        $validator = Validator::make($data, [
            "phonebook_group_id" => "required|exists:phonebook_groups,id",
            "name" => 'required|string',
            "number" => 'required',
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
            $phonebook =  Phonebook::create($data);
            DB::commit();
            return $phonebook->refresh();
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
            $phonebook = self::getById($id);
            $phonebook->update($data);
            DB::commit();
            return $phonebook->refresh();
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
    public static function deleteMultiple(array $data)
    {
        DB::beginTransaction();
        try {
            $validator = Validator::make($data, [
                "id" => "required|array",
                "id.*" => "exists:phonebooks,id",
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            Phonebook::whereIn("id", $data["id"])->delete();
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
    public static function validateImport(array $data)
    {
        $validator = Validator::make($data, [
            "file" => "required|file|mimes:xlsx,xls"
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator->validated();
    }

    public function importCsv(array $data)
    {
        DB::beginTransaction();
        try {
            $data = self::validateImport($data);

            if (!empty($file = $data["file"] ?? null)) {
                $this->import_file = $this->file_service->saveFromFile($file, FileConstants::PHONEBOOK_IMPORT_PATH, null, auth()->id());
            }

            $path = storage_path() . "/" . $this->import_file->path;
            Excel::import(new PhonebookImport(), $path);
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            $this->file_service->cleanDelete($this->import_file?->id);
            throw $th;
        }
    }

    public static function validateWriteUpload(array $data)
    {
        $validator = Validator::make($data, [
            "phonebook_group_id" => "required|exists:phonebook_groups,id",
            "content" => "required|string"
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator->validated();
    }

    public function writeUpload(array $data)
    {
        DB::beginTransaction();
        try {
            $data = self::validateWriteUpload($data);
            $content = $data["content"];
            $pairs = explode(",", $content);

            foreach ($pairs as $pair) {
                $lastSpacePos = strrpos($pair, ' ');
                if ($lastSpacePos !== false) {
                    $name = substr($pair, 0, $lastSpacePos);
                    $number = substr($pair, $lastSpacePos + 1);
                    $result[] = ["name" => $name, "number" => $number];
                }
            }

            foreach ($result as $key => $result_) {
                self::create([
                    "phonebook_group_id" => $data["phonebook_group_id"],
                    "name" => trim($result_["name"]),
                    "number" => trim($result_["number"]),
                ]);
            }

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function list()
    {
        return Phonebook::latest();
    }
}
