<?php

namespace App\Services\General\Guzzle;

use App\Exceptions\General\GuzzleException;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use Throwable;

class GuzzleService
{
    public string $url;
    public array $headers, $configs;
    public Client $client;

    public function __construct(array $headers = [], array $configs = [])
    {
        $this->configs = $configs;

        $this->headers = array_merge(
            ['Accept' => 'application/json'],
            $this->useContentType(),
            $headers
        );

        $this->client = new Client(['verify' => false]);
    }


    public function post($url, array $data = [])
    {
        try {
            $response = $this->client->post(
                $url,
                [
                    'headers' => $this->headers,
                    !empty($this->configs["body_key"] ?? null) ? $this->configs["body_key"] : "json" => $data
                ]
            );

            return $this->success($response);
        } catch (Throwable $e) {
            return $this->error($e);
        }
    }

    public function get($url, array $data = [])
    {
        try {
            $response = $this->client->get(
                $url,
                [
                    'headers' => $this->headers,
                    'json' => $data,
                ]
            );
            return $this->success($response);
        } catch (Throwable $e) {
            return $this->error($e);
        }
    }

    public function getWithQuery($url, array $data = [])
    {
        try {
            $response = $this->client->get(
                $url,
                [
                    'headers' => $this->headers,
                    'query' => $data,
                ]
            );

            return $this->success($response);
        } catch (Throwable $e) {
            return $this->error($e);
        }
    }

    public function delete($url)
    {
        try {
            $response = $this->client->delete(
                $url,
                [
                    'headers' => $this->headers,
                ]
            );

            return $this->success($response);
        } catch (Throwable $e) {
            // dd($e->getMessage());
            return $this->error($e);
        }
    }

    private function success($response)
    {
        $body = $response->getBody();
        return self::response(
            $response->getReasonPhrase(),
            $response->getStatusCode(),
            (json_decode((string) $body->getContents(), true))
        );
    }

    private function error(Throwable $e)
    {
        try {
            throw $e;
        } catch (BadResponseException $e) {
            return self::response($e->getResponse()->getBody()->getContents(), $e->getCode());
        } catch (Throwable $e) {
            return self::response($e->getMessage(), $e->getCode());
        }
    }

    private function response($message, $status, $data = null)
    {
        return [
            "status" => $status,
            "message" => $message,
            "data" => $data
        ];
    }

    public function validateResponse(array $process)
    {
        if ($process["status"] == 0) {
            throw new GuzzleException("Unable to connect to remote server. Kindly check your internet connection and retry.");
        }
    }

    public function useContentType()
    {
        return (!isset($this->configs["use_content_type"]) || $this->configs["use_content_type"])
            ? ['Content-Type' => 'application/json']
            : [];
    }
}
