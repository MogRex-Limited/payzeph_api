<?php

namespace App\Http\Controllers\Api\V1\User\Finance\Payment;

use App\Constants\Finance\PaymentConstants;
use App\Constants\General\ApiConstants;
use App\Exceptions\Finance\Payment\PaymentException;
use App\Exceptions\Finance\Payment\SquadException;
use App\Helpers\ApiHelper;
use App\Http\Controllers\Controller;
use App\Services\Finance\Payment\Bank\BankPaymentService;
use App\Services\Finance\Payment\PaymentService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PaymentController extends Controller
{

    function __construct()
    {
    }

    public function initiate(Request $request)
    {
        $data = $request->validate([
            "method" => "required|string|" . Rule::requiredIf(array_keys(PaymentConstants::PAYMENT_OPTIONS))
        ]);

        if (in_array($data["method"], [PaymentConstants::PAY_WITH_BANK])) {
            return $this->initiateWithBank($request);
        } elseif (in_array($data["method"], [PaymentConstants::PAY_WITH_CARD])) {
            return $this->initiateWithCard($request);
        }
    }

    public function initiateWithCard(Request $request)
    {
        try {
            $response = (new PaymentService(auth()->user()))
                ->validate($request->all())
                ->initiate();
            return ApiHelper::validResponse("Payment initiated successful", $response);
        } catch (ValidationException $e) {
            return ApiHelper::inputErrorResponse("The given data is invalid", ApiConstants::VALIDATION_ERR_CODE, $e);
        } catch (SquadException | PaymentException $e) {
            return ApiHelper::problemResponse($e->getMessage(), ApiConstants::BAD_REQ_ERR_CODE, $e);
        } catch (Exception $e) {
            return ApiHelper::problemResponse($this->serverErrorMessage, ApiConstants::SERVER_ERR_CODE, $e);
        }
    }

    public function initiateWithBank(Request $request)
    {
        try {
            (new BankPaymentService())->submitProof($request->all());
            return ApiHelper::validResponse("Payment initiated successful");
        } catch (ValidationException $e) {
            return ApiHelper::inputErrorResponse("The given data is invalid", ApiConstants::VALIDATION_ERR_CODE, $e);
        } catch (Exception $e) {
            return ApiHelper::problemResponse($this->serverErrorMessage, ApiConstants::SERVER_ERR_CODE, $e);
        }
    }
}
