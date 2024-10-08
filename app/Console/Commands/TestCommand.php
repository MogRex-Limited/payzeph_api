<?php

namespace App\Console\Commands;

use App\Services\Finance\Pricing\PricingService;
use App\Services\Notification\AppMailerService;
use Illuminate\Console\Command;

class TestCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $response =  (new PricingService)->calculate([
            "quantity" => "100",
            "type" => "money"
        ]);

        // dd($response);

        // AppMailerService::send([
        //     "data" => [
        //     ],
        //     "to" => "joelomojefe@gmail.com",
        //     "template" => "emails.test",
        //     "subject" => "Test Email",
        // ]);
    }
}
