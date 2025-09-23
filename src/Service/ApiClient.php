<?php

declare(strict_types=1);

namespace BatteryIncludedSdk\Service;

class ApiClient
{
    public function __construct(private string $baseUrl, private string $collection, private string $apiKey)
    {
    }

    public function postJson(string $urlPart, string $data): Response
    {
        $url = $this->baseUrl . $this->collection . $urlPart;

        return $this->send($url, 'POST', 'json', $data);
    }

    public function postNDJson(string $urlPart, string $data): Response
    {
        $url = $this->baseUrl . $this->collection . $urlPart;

        return $this->send($url, 'POST', 'x-ndjson', $data);
    }

    public function patchNDJson(string $urlPart, string $data): Response
    {
        $url = $this->baseUrl . $this->collection . $urlPart;

        return $this->send($url, 'PATCH', 'x-ndjson', $data);
    }

    public function deleteJson(string $urlPart, string $data): Response
    {
        $url = $this->baseUrl . $this->collection . $urlPart;

        return $this->send($url, 'DELETE', 'json', $data);
    }

    public function send(string $url, string $method, string $contentType, string $data): Response
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        switch ($method) {
            case 'POST':
                curl_setopt($ch, CURLOPT_POST, true);
                break;
            case 'DELETE':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
                break;
            case 'PATCH':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
                break;
            default:
                throw new \InvalidArgumentException('Unsupported method: ' . $method);
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->getHeaders($contentType));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            throw new \Exception(curl_error($ch));
        }

        return new Response($response);
    }

    private function getHeaders(string $contentType): array
    {
        return [
            "Content-Type: application/" . $contentType,
            "X-BI-API-KEY: " . $this->apiKey,
        ];
    }
}
