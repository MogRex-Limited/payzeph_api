<?php

namespace App\Services\Finance\Provider\Squad;

use App\Models\Currency;
use App\Models\User;
use App\Services\Finance\Provider\Squad\SquadChargeWebhookService;
use App\Services\System\ExceptionService;
use Exception;
use Illuminate\Support\Facades\DB;

class SquadWebhookService
{
    public array $payload;
    public $event;
    public Currency $currency;
    public User $user;
    public $model;

    public function setPayload(array $value)
    {
        $this->payload = $value;
        return $this;
    }

    public function handle()
    {
        DB::beginTransaction();
        try {
            $response = $this->handleActionsByEvents();
            DB::commit();
            return $response;
        } catch (Exception $e) {
            DB::rollBack();
            ExceptionService::logAndBroadcast($e);
            throw $e;
        }
    }

    public function handleActionsByEvents()
    {
        $payload = $this->payload;
        if (in_array($payload["event"] ?? null, ["charge_successful"])) {
            return $this->handleCardAndOtherPayments($payload);
        }
    }

    public function handleCardAndOtherPayments($payload)
    {
        return (new SquadChargeWebhookService)
            ->setPayload($payload)->handle();
    }
}
