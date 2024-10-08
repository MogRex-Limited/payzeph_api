<?php

namespace App\Http\Controllers\Webhook;

use App\Constants\General\ApiConstants;
use App\Exceptions\Finance\Payment\SquadException;
use App\Exceptions\Finance\Transaction\TransactionException;
use App\Helpers\ApiHelper;
use App\Http\Controllers\Controller;
use App\Services\Finance\Provider\Squad\SquadService;
use App\Services\Finance\Provider\Squad\SquadWebhookService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SquadWehbookHandlingController extends Controller
{
    public function handleWebhook(Request $request)
    {
        // Log the start of the function
        Log::info("Webhook request received", $request->all());

        // Retrieve the request's body
        $input = $request->getContent();

        // Check for JSON decoding errors
        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error("Invalid JSON: " . json_last_error_msg());
            return response()->json([
                'status' => 400,
                'success' => false,
                'message' => 'Invalid JSON',
                'data' => []
            ], 400);
        }


        $squad_secret_key = (new SquadService)->api_key;

        logger("Squad Headers", [
            "headers" => $request->headers,
            "input" => $input,
        ]);

        $squad_signature = $request->headers->get('x-squad-signature');
        $encrypted_body = $request->headers->get('x-squad-encrypted-body');

        if (empty($squad_signature) && empty($encrypted_body)) {
            // Neither x-squad-signature nor x-encrypted-body headers present
            Log::error("Invalid Squad request: Missing both x-squad-signature and x-encrypted-body");
            return response()->json([
                'status' => 400,
                'success' => false,
                'message' => 'Invalid Squad request: Missing both x-squad-signature and x-encrypted-body',
                'data' => []
            ], 400);
        }

        if (!empty($squad_signature)) {
            $calculated_signature = hash_hmac('sha512', $input, $squad_secret_key);

            if (!hash_equals($squad_signature, $calculated_signature)) {
                // Invalid Squad signature, handle the error
                Log::error("Invalid Squad signature", [
                    "signature" => $squad_signature
                ]);
                return response()->json([
                    'status' => 400,
                    'success' => false,
                    'message' => 'Invalid Squad signature',
                    'data' => []
                ], 400);
            }
        }

        if (!empty($encrypted_body)) {
            $calculated_signature_ = hash_hmac('sha512', $input, $squad_secret_key);

            logger("Encrypted body", [
                "encrypted_body" => $encrypted_body,
                "calculated_signature_" => strtoupper($calculated_signature_),
            ]);

            if (!hash_equals($encrypted_body, strtoupper($calculated_signature_))) {
                // Invalid Squad encrypted body, handle the error
                Log::error("Invalid Squad encrypted body", [
                    "body" => $encrypted_body
                ]);
                return response()->json([
                    'status' => 400,
                    'success' => false,
                    'message' => 'Invalid Squad encrypted body',
                    'data' => []
                ], 400);
            }
        }

        // Handle the charge webhook
        return $this->handleChargeWebhook($request);
    }

    public function handleChargeWebhook(Request $request)
    {
        try {
            $service = new SquadWebhookService;
            $service->setPayload($request->all())->handle();
            return ApiHelper::validResponse("Charge Hook Received");
        } catch (SquadException | TransactionException $e) {
            return ApiHelper::problemResponse($e->getMessage(), ApiConstants::BAD_REQ_ERR_CODE);
        } catch (Exception $e) {
            return ApiHelper::problemResponse($this->serverErrorMessage, ApiConstants::SERVER_ERR_CODE);
        }
    }
}
