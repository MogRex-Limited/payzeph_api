<?php

namespace App\Http\Controllers\Api\V1\User\Finance\Transaction;

use App\Constants\General\ApiConstants;
use App\Exceptions\General\ModelNotFoundException;
use App\Helpers\ApiHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\Finance\Transaction\TransactionResource;
use App\QueryBuilders\Finance\TransactionQueryBuilder;
use App\Services\Finance\Transaction\TransactionService;
use Exception;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    protected $transaction_service;

    function __construct()
    {
        $this->transaction_service = new TransactionService;
    }
    public function index(Request $request)
    {
        try {
            $transactions = TransactionQueryBuilder::filterList($request)->latest()->get();
            $data = TransactionResource::collection($transactions);
            return ApiHelper::validResponse("Transactions returned successfully", $data);
        } catch (Exception $th) {
            //throw $th;
            return ApiHelper::problemResponse("Something went wrong while trying to process your request", ApiConstants::SERVER_ERR_CODE, $th);
        }
    }

    public function show($id)
    {
        try {
            $transaction = $this->transaction_service->getById($id);
            $data = TransactionResource::make($transaction);
            return ApiHelper::validResponse("Transaction details returned successfully", $data);
        } catch (ModelNotFoundException $th) {
            return ApiHelper::problemResponse($th->getMessage(), ApiConstants::BAD_REQ_ERR_CODE, $th);
        } catch (Exception $th) {
            return ApiHelper::problemResponse("Something went wrong while trying to process your request", ApiConstants::SERVER_ERR_CODE, $th);
        }
    }
}
