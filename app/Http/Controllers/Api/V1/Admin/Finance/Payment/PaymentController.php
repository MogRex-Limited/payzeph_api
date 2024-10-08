<?php

namespace App\Http\Controllers\Api\V1\Admin\Finance\Payment;

use App\Constants\Finance\PaymentConstants;
use App\Constants\General\ApiConstants;
use App\Exceptions\Finance\Payment\PaymentException;
use App\Exceptions\Finance\Payment\SquadException;
use App\Helpers\ApiHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\Finance\Payment\BankPaymentResource;
use App\Services\Finance\Payment\Bank\BankPaymentService;
use App\Services\Finance\Payment\PaymentService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PaymentController extends Controller
{
    protected $bank_payment_service;

    function __construct()
    {
        $this->bank_payment_service = new BankPaymentService;
    }

    public function index()
    {
        try {
            $bank_payment_proofs = $this->bank_payment_service->list()->get();
            $data = BankPaymentResource::make($bank_payment_proofs);
            return ApiHelper::validResponse("Bank Payment proof returned successfully", $data);
        } catch (Exception $e) {
            return ApiHelper::problemResponse($this->serverErrorMessage, ApiConstants::SERVER_ERR_CODE, $e);
        }
    }

    public function update(Request $request)
    {
        try {
            $response = (new BankPaymentService)->update($request->all());
            return ApiHelper::validResponse("Payment initiated successful", $response);
        } catch (ValidationException $e) {
            return ApiHelper::inputErrorResponse($this->validationErrorMessage, ApiConstants::VALIDATION_ERR_CODE, $e);
        } catch (SquadException | PaymentException $e) {
            return ApiHelper::problemResponse($e->getMessage(), ApiConstants::BAD_REQ_ERR_CODE, $e);
        } catch (Exception $e) {
            return ApiHelper::problemResponse($this->serverErrorMessage, ApiConstants::SERVER_ERR_CODE, $e);
        }
    }
}
