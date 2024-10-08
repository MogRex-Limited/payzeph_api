<?php

namespace App\Imports\Phonebook;

use App\Services\Phonebook\PhonebookGroupService;
use App\Services\Phonebook\PhonebookService;
use Maatwebsite\Excel\Concerns\RemembersRowNumber;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class PhonebookImport implements ToModel, WithHeadingRow, WithValidation, SkipsEmptyRows, WithStartRow
{
    use RemembersRowNumber;
    protected $phonebook_service;
    protected $phonebook_group_service;
    protected $row_number;

    public function __construct()
    {
        $this->row_number = $this->getRowNumber();
        $this->phonebook_service = new PhonebookService;
        $this->phonebook_group_service = new PhonebookGroupService;
    }

    public function startRow(): int
    {
        return 2;
    }

    public function isEmptyWhen(array $row): bool
    {
        return (blank($row['number']));
    }

    public function model(array $row)
    {
        $row["phonebook_group_id"] = !empty($row["group_code"] ?? null) ? $this->fetchGroupId($row)?->id : null;
        $phonebook = $this->phonebook_service->create($row);
        return $phonebook;
    }

    public function rules(): array
    {
        return [
            "group_code" => "required|exists:phonebook_groups,identifier",
            "name" => "required|string",
            "number" => "required",
        ];
    }

    function fetchGroupId(array $row)
    {
        $group = $this->phonebook_group_service->getById($row["group_code"], "identifier");
        return $group;
    }

    /**
     * @return array
     */
    public function customValidationMessages()
    {
        return [
            'name.required' => 'The name field is required.',
            'name.string' => 'The name must be a string.',
            'number.required' => 'The number field is required.',
        ];
    }
}
