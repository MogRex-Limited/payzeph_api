<?php

namespace App\Http\Controllers\Api\V1\User\Messaging;

use App\Constants\General\ApiConstants;
use App\Exceptions\General\InvalidRequestException;
use App\Exceptions\General\ModelNotFoundException;
use App\Exceptions\Messaging\MessagingException;
use App\Helpers\ApiHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\Messaging\MessageResource;
use App\Services\Messaging\MessageService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class MessageController extends Controller
{
    protected $message_service;

    function __construct()
    {
        $this->message_service = new MessageService(auth()->user());
    }

    public function index(Request $request)
    {
        try {
            $user = auth()->user();
            $messages = (new MessageService($user))->list($request->all())->get();
            $data = MessageResource::collection($messages);
            return ApiHelper::validResponse("Messages returned successfully", $data);
        } catch (Exception $th) {
            //throw $th;
            return ApiHelper::problemResponse($this->serverErrorMessage, ApiConstants::SERVER_ERR_CODE, $th);
        }
    }

    public function show($id)
    {
        try {
            $message = $this->message_service->getById($id, "uuid");
            $data = MessageResource::make($message);
            return ApiHelper::validResponse("Messages details returned successfully", $data);
        } catch (Exception $th) {
            return ApiHelper::problemResponse($this->serverErrorMessage, ApiConstants::SERVER_ERR_CODE, $th);
        }
    }

    public function send(Request $request)
    {
        try {
            $message = (new MessageService(auth()->user()))->setProvider()->send($request->all());
            $data = MessageResource::make($message);
            return ApiHelper::validResponse("Message created successfully", $data);
        } catch (ValidationException $th) {
            return ApiHelper::inputErrorResponse($this->validationErrorMessage, ApiConstants::VALIDATION_ERR_CODE, $th);
        } catch (ModelNotFoundException | InvalidRequestException | MessagingException $th) {
            return ApiHelper::problemResponse($th->getMessage(), ApiConstants::BAD_REQ_ERR_CODE, $th);
        } catch (Exception $th) {
            return ApiHelper::problemResponse($this->serverErrorMessage, ApiConstants::SERVER_ERR_CODE, $th);
        }
    }

    public function sendViaTemplate(Request $request)
    {
        try {
            $message = (new MessageService(auth()->user()))->setProvider()->sendViaTemplate($request->all());
            $data = MessageResource::make($message);
            return ApiHelper::validResponse("Message created successfully", $data);
        } catch (ValidationException $th) {
            return ApiHelper::inputErrorResponse($this->validationErrorMessage, ApiConstants::VALIDATION_ERR_CODE, $th);
        } catch (ModelNotFoundException | InvalidRequestException | MessagingException $th) {
            return ApiHelper::problemResponse($th->getMessage(), ApiConstants::BAD_REQ_ERR_CODE, $th);
        } catch (Exception $th) {
            return ApiHelper::problemResponse($this->serverErrorMessage, ApiConstants::SERVER_ERR_CODE, $th);
        }
    }
}
