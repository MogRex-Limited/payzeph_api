<?php

namespace App\Http\Controllers\Api\V1\User\Phonebook;

use App\Constants\General\ApiConstants;
use App\Exceptions\General\InvalidRequestException;
use App\Exceptions\General\ModelNotFoundException;
use App\Helpers\ApiHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\Phonebook\PhonebookGroupResource;
use App\Services\Phonebook\PhonebookGroupService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PhonebookGroupController extends Controller
{
    protected $phonebook_group_service;

    function __construct()
    {
        $this->phonebook_group_service = new PhonebookGroupService;
    }

    public function index(Request $request)
    {
        try {
            $sender_identifiers = $this->phonebook_group_service->list()->get();
            $data = PhonebookGroupResource::collection($sender_identifiers);
            return ApiHelper::validResponse("Phonebook groups returned successfully", $data);
        } catch (Exception $th) {
            //throw $th;
            return ApiHelper::problemResponse($this->serverErrorMessage, ApiConstants::SERVER_ERR_CODE, $th);
        }
    }

    public function show($id)
    {
        try {
            $sender_identifier = $this->phonebook_group_service->getById($id);
            $data = PhonebookGroupResource::make($sender_identifier);
            return ApiHelper::validResponse("Phonebook group details returned successfully", $data);
        } catch (Exception $th) {
            return ApiHelper::problemResponse($this->serverErrorMessage, ApiConstants::SERVER_ERR_CODE, $th);
        }
    }

    public function store(Request $request)
    {
        try {
            $sender_identifier = $this->phonebook_group_service->create($request->all());
            $data = PhonebookGroupResource::make($sender_identifier);
            return ApiHelper::validResponse("Phonebook group created successfully", $data);
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
            $sender_identifier = $this->phonebook_group_service->update($request->all(), $id);
            $data = PhonebookGroupResource::make($sender_identifier);
            return ApiHelper::validResponse("Phonebook group updated successfully", $data);
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

    public function destroy($id)
    {
        try {
            $sender_identifier = $this->phonebook_group_service->getById($id);
            $sender_identifier->delete();
            return ApiHelper::validResponse("Phonebook group deleted successfully");
        } catch (ModelNotFoundException $th) {
            return ApiHelper::problemResponse($th->getMessage(), ApiConstants::BAD_REQ_ERR_CODE, $th);
        } catch (Exception $th) {
            return ApiHelper::problemResponse($this->serverErrorMessage, ApiConstants::SERVER_ERR_CODE, $th);
        }
    }
}
