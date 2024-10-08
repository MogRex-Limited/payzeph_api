<?php

namespace App\Http\Controllers\Api\V1\Admin\Profile;

use App\Constants\General\ApiConstants;
use App\Exceptions\General\ModelNotFoundException;
use App\Helpers\ApiHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\AdminResource;
use App\Services\Admin\AdminService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ProfileController extends Controller
{
    protected $admin_service;

    function __construct()
    {
        $this->admin_service = new AdminService;
    }
    public function show()
    {
        try {
            $admin = auth("admin")->user();
            $data = AdminResource::make($admin);
            return ApiHelper::validResponse("Admin details returned successfully", $data);
        } catch (Exception $th) {
            return ApiHelper::problemResponse($this->serverErrorMessage, ApiConstants::SERVER_ERR_CODE, $th);
        }
    }

    public function update(Request $request)
    {
        try {
            $admin = $this->admin_service->update($request->all(), auth("admin")->id());
            $data = AdminResource::make($admin);
            return ApiHelper::validResponse("Admin updated successfully", $data);
        } catch (ValidationException $th) {
            return ApiHelper::inputErrorResponse($this->validationErrorMessage, ApiConstants::VALIDATION_ERR_CODE, $th);
        } catch (ModelNotFoundException $th) {
            return ApiHelper::problemResponse($th->getMessage(), ApiConstants::BAD_REQ_ERR_CODE, $th);
        } catch (Exception $th) {
            return ApiHelper::problemResponse($this->serverErrorMessage, ApiConstants::SERVER_ERR_CODE, $th);
        }
    }
}
