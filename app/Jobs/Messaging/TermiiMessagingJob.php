<?php

namespace App\Jobs\Messaging;

use App\Events\Notification\App\AppNotificationStatusEvent;
use App\Services\General\Guzzle\GuzzleService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class TermiiMessagingJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $params;
    public $post_data;
    public $url;
    public $headers;
    public $configs;
    public $meta_data;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $params)
    {
        $this->params = $params;
        $this->post_data = $params["post_data"];
        $this->url = $params["url"];
        $this->headers = $params["headers"];
        $this->configs = $params["configs"];
        $this->meta_data = $params["meta_data"];
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        
        try {
            $response = $this->termii_service->messagingData([
                "to" => $this->validated_data["recipient"],
                "from" => $this->sender->identifier,
                "sms" => $this->validated_data["content"],
                "type" => "plain",
                "channel" => "generic",
            ])->sendSingleMessage();

            if ($response["code"] !== "ok") {
                throw new MessagingException("Failed to send message. Please try again");
            }
        } catch (\Exception $e) {
            logger($e->getMessage(), $e->getTrace());
            // throw $e;
        }
    }
}
