<?php

namespace App\Http\Controllers\Api\V1\Admin\SenderIdentifier;

use App\Constants\General\ApiConstants;
use App\Exceptions\General\InvalidRequestException;
use App\Exceptions\General\ModelNotFoundException;
use App\Helpers\ApiHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\SenderIdentifier\SenderIdentifierResource;
use App\Services\SenderIdentifier\SenderIdentifierService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\Translation\Exception\ProviderException;

class SenderIdentifierController extends Controller
{
    protected $sender_identifier_service;

    function __construct()
    {
        $this->sender_identifier_service = new SenderIdentifierService;
    }

    public function index(Request $request)
    {
        try {
            $sender_identifiers = $this->sender_identifier_service->list()->get();
            $data = SenderIdentifierResource::collection($sender_identifiers);
            return ApiHelper::validResponse("Indentifiers returned successfully", $data);
        } catch (Exception $th) {
            //throw $th;
            return ApiHelper::problemResponse($this->serverErrorMessage, ApiConstants::SERVER_ERR_CODE, $th);
        }
    }

    public function show($id)
    {
        try {
            $sender_identifier = $this->sender_identifier_service->getById($id);
            $data = SenderIdentifierResource::make($sender_identifier);
            return ApiHelper::validResponse("Indentifier details returned successfully", $data);
        } catch (Exception $th) {
            return ApiHelper::problemResponse($this->serverErrorMessage, ApiConstants::SERVER_ERR_CODE, $th);
        }
    }

    public function store(Request $request)
    {
        try {
            $sender_identifier = $this->sender_identifier_service->create($request->all());
            $data = SenderIdentifierResource::make($sender_identifier);
            return ApiHelper::validResponse("Indentifier created successfully", $data);
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
            $sender_identifier = $this->sender_identifier_service->update($request->all(), $id);
            $data = SenderIdentifierResource::make($sender_identifier);
            return ApiHelper::validResponse("Indentifier updated successfully", $data);
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
            $sender_identifier = $this->sender_identifier_service->getById($id);
            $sender_identifier->delete();
            return ApiHelper::validResponse("Indentifier deleted successfully");
        } catch (ModelNotFoundException $th) {
            return ApiHelper::problemResponse($th->getMessage(), ApiConstants::BAD_REQ_ERR_CODE, $th);
        } catch (Exception $th) {
            return ApiHelper::problemResponse($this->serverErrorMessage, ApiConstants::SERVER_ERR_CODE, $th);
        }
    }

    public function sendToProvider(Request $request, $id)
    {
        try {
            $sender_identifier = $this->sender_identifier_service->sendToProvider($id);
            $data = SenderIdentifierResource::make($sender_identifier);
            return ApiHelper::validResponse("Indentifier updated successfully", $data);
        } catch (ValidationException $th) {
            return ApiHelper::inputErrorResponse($this->validationErrorMessage, ApiConstants::VALIDATION_ERR_CODE, $th);
        } catch (ModelNotFoundException $th) {
            return ApiHelper::problemResponse($th->getMessage(), ApiConstants::BAD_REQ_ERR_CODE, $th);
        } catch (InvalidRequestException | ProviderException $th) {
            return ApiHelper::problemResponse($th->getMessage(), ApiConstants::BAD_REQ_ERR_CODE, $th);
        } catch (Exception $th) {
            return ApiHelper::problemResponse($this->serverErrorMessage, ApiConstants::SERVER_ERR_CODE, $th);
        }
    }
}
