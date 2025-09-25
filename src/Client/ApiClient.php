<?php

declare(strict_types=1);

namespace BatteryIncludedSdk\Client;

use BatteryIncludedSdk\Service\Response;

class ApiClient
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private string $baseUrl,
        private string $collection,
        private string $apiKey,
    ) {
    }

    public function getJson(string $urlPart, array $parameters): Response
    {
        $url = $this->baseUrl . $this->collection . $urlPart;

        return $this->httpClient->send($url, 'GET', $this->getHeaders('json'), '');
    }

    public function postJson(string $urlPart, string $data): Response
    {
        $url = $this->baseUrl . $this->collection . $urlPart;

        return $this->httpClient->send($url, 'POST', $this->getHeaders('json'), $data);
    }

    public function postNDJson(string $urlPart, string $data): Response
    {
        $url = $this->baseUrl . $this->collection . $urlPart;

        return $this->httpClient->send($url, 'POST', $this->getHeaders('x-ndjson'), $data);
    }

    public function patchNDJson(string $urlPart, string $data): Response
    {
        $url = $this->baseUrl . $this->collection . $urlPart;

        return $this->httpClient->send($url, 'PATCH', $this->getHeaders('x-ndjson'), $data);
    }

    public function deleteJson(string $urlPart, string $data): Response
    {
        $url = $this->baseUrl . $this->collection . $urlPart;

        return $this->httpClient->send($url, 'DELETE', $this->getHeaders('json'), $data);
    }

    private function getHeaders(string $contentType): array
    {
        return [
            'Content-Type: application/' . $contentType,
            'X-BI-API-KEY: ' . $this->apiKey,
        ];
    }
}
