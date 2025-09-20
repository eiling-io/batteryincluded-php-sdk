<?php

declare(strict_types=1);

namespace BatteryIncludedSdk\Service;

class ApiClient
{
    public function __construct(private string $baseUrl, private string $collection, private string $apiKey)
    {
    }

    public function post(string $urlPart, string $data): Response
    {
        $url = $this->baseUrl . $this->collection . $urlPart;

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->getHeaders());
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            throw new \Exception(curl_error($ch));
        }

        return new Response($response);
    }

    private function getHeaders()
    {
        return [
            "Content-Type: application/x-ndjson",
            "X-BI-API-KEY: " . $this->apiKey,
        ];
    }
}
