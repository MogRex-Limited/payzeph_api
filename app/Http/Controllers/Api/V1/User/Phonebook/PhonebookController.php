<?php

namespace App\Http\Controllers\Api\V1\User\Phonebook;

use App\Constants\General\ApiConstants;
use App\Exceptions\General\InvalidRequestException;
use App\Exceptions\General\ModelNotFoundException;
use App\Helpers\ApiHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\Phonebook\PhonebookResource;
use App\Services\Phonebook\PhonebookService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PhonebookController extends Controller
{
    protected $phonebook_service;

    function __construct()
    {
        $this->phonebook_service = new PhonebookService;
    }

    public function index(Request $request)
    {
        try {
            $sender_identifiers = $this->phonebook_service->list()->get();
            $data = PhonebookResource::collection($sender_identifiers);
            return ApiHelper::validResponse("Phonebook returned successfully", $data);
        } catch (Exception $th) {
            //throw $th;
            return ApiHelper::problemResponse($this->serverErrorMessage, ApiConstants::SERVER_ERR_CODE, $th);
        }
    }

    public function show($id)
    {
        try {
            $phonebook = $this->phonebook_service->getById($id);
            $data = PhonebookResource::make($phonebook);
            return ApiHelper::validResponse("Phonebook details returned successfully", $data);
        } catch (Exception $th) {
            return ApiHelper::problemResponse($this->serverErrorMessage, ApiConstants::SERVER_ERR_CODE, $th);
        }
    }

    public function store(Request $request)
    {
        try {
            $phonebook = $this->phonebook_service->create($request->all());
            $data = PhonebookResource::make($phonebook);
            return ApiHelper::validResponse("Phonebook item created successfully", $data);
        } catch (ValidationException $th) {
            return ApiHelper::inputErrorResponse($this->validationErrorMessage, ApiConstants::VALIDATION_ERR_CODE, $th);
        } catch (ModelNotFoundException $th) {
            return ApiHelper::problemResponse($th->getMessage(), ApiConstants::BAD_REQ_ERR_CODE, $th);
        } catch (Exception $th) {
            return ApiHelper::problemResponse($this->serverErrorMessage, ApiConstants::SERVER_ERR_CODE, $th);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $phonebook = $this->phonebook_service->update($request->all(), $id);
            $data = PhonebookResource::make($phonebook);
            return ApiHelper::validResponse("Phonebook item updated successfully", $data);
        } catch (ValidationException $th) {
            return ApiHelper::inputErrorResponse($this->validationErrorMessage, ApiConstants::VALIDATION_ERR_CODE, $th);
        } catch (ModelNotFoundException $th) {
            return ApiHelper::problemResponse($th->getMessage(), ApiConstants::BAD_REQ_ERR_CODE, $th);
        } catch (InvalidRequestException $th) {
            return ApiHelper::problemResponse($th->getMessage(), ApiConstants::BAD_REQ_ERR_CODE, $th);
        } catch (Exception $th) {
            return ApiHelper::problemResponse($this->serverErrorMessage, ApiConstants::SERVER_ERR_CODE, $th);
        }
    }

    public function deleteItems(Request $request)
    {
        try {
            $this->phonebook_service->deleteMultiple($request->all());
            return ApiHelper::validResponse("Phonebook items deleted successfully");
        } catch (ModelNotFoundException $th) {
            return ApiHelper::problemResponse($th->getMessage(), ApiConstants::BAD_REQ_ERR_CODE, $th);
        } catch (Exception $th) {
            return ApiHelper::problemResponse($this->serverErrorMessage, ApiConstants::SERVER_ERR_CODE, $th);
        }
    }

    public function importCsv(Request $request)
    {
        try {
            $this->phonebook_service->importCsv($request->all());
            return ApiHelper::validResponse("Contact imported successfully");
        } catch (ValidationException $e) {
            return ApiHelper::inputErrorResponse($this->validationErrorMessage, ApiConstants::VALIDATION_ERR_CODE, $e);
        } catch (ModelNotFoundException $th) {
            return ApiHelper::problemResponse($th->getMessage(), ApiConstants::BAD_REQ_ERR_CODE, $th);
        } catch (Exception $th) {
            return ApiHelper::problemResponse($this->serverErrorMessage, ApiConstants::SERVER_ERR_CODE, $th);
        }
    }

    public function writeUpload(Request $request)
    {
        try {
            $this->phonebook_service->writeUpload($request->all());
            return ApiHelper::validResponse("Contact saved successfully");
        } catch (ValidationException $e) {
            return ApiHelper::inputErrorResponse($this->validationErrorMessage, ApiConstants::VALIDATION_ERR_CODE, $e);
        } catch (ModelNotFoundException $th) {
            return ApiHelper::problemResponse($th->getMessage(), ApiConstants::BAD_REQ_ERR_CODE, $th);
        } catch (Exception $th) {
            return ApiHelper::problemResponse($this->serverErrorMessage, ApiConstants::SERVER_ERR_CODE, $th);
        }
    }
}
