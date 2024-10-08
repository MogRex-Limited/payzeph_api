<?php

namespace App\Jobs\Messaging;

use App\Events\Notification\App\AppNotificationStatusEvent;
use App\Services\General\Guzzle\GuzzleService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class FcmMessagingJob implements ShouldQueue
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
            foreach ($this->post_data as $key => $data) {
                $service = new GuzzleService($this->url, $this->headers);
                $response = $service->post(array_merge($data, $this->configs));
                
                event(new AppNotificationStatusEvent([
                    "response" => $response,
                    "meta_data" => $this->meta_data,
                ]));
            }

        } catch (\Exception $e) {
            logger($e->getMessage(), $e->getTrace());
            // throw $e;
        }
    }
}
