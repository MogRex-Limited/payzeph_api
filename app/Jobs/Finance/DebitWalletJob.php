<?php

namespace App\Jobs\Finance;

use App\Services\Finance\Wallet\WalletService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Throwable;

class DebitWalletJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected WalletService $wallet_service;
    protected float $amount;

    /**
     * Create a new job instance.
     */
    public function __construct(WalletService $wallet_service, float $amount)
    {
        $this->wallet_service = $wallet_service;
        $this->amount = $amount;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->wallet_service->debit($this->amount);
    }

    public function failed(Throwable $e)
    {
        logger("Failed to dispatch job", [
            "message" => $e->getMessage(),
            "trace" => $e->getTrace(),
        ]);
    }
}
