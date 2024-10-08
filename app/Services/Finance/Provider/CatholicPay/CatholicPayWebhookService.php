<?php

namespace App\Services\Finance\Provider\CatholicPay;

use App\Models\Currency;
use App\Models\User;


class CatholicPayWebhookService
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
       return $this->handleActionsByEvents();
    }

    public function handleActionsByEvents()
    {
        $payload = $this->payload;
        if (in_array(($payload["event"] ?? null), ["charge_successful"])) {
            return $this->handleCardAndOtherPayments($payload);
        }
    }

    public function handleCardAndOtherPayments($payload)
    {
        return (new CatholicPayChargeWebhookService)
            ->setPayload($payload)->handle();
    }
}
