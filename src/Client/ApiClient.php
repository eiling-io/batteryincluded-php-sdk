<?php

declare(strict_types=1);

namespace BatteryIncludedSdk\Client;

use BatteryIncludedSdk\Service\Response;
use Composer\InstalledVersions;

class ApiClient
{
    public const PACKAGE = 'batteryincluded/batteryincluded-php-sdk';

    public function __construct(
        private HttpClientInterface $httpClient,
        private string $baseUrl,
        private string $collection,
        private string $apiKey,
    ) {
    }

    public function getJson(string $urlPart): Response
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

    /**
     * Version of the installed SDK, `dev` outside of composer.
     */
    public static function getVersion(): string
    {
        try {
            if (class_exists(InstalledVersions::class) && InstalledVersions::isInstalled(self::PACKAGE)) {
                return InstalledVersions::getPrettyVersion(self::PACKAGE) ?? 'dev';
            }
        } catch (\OutOfBoundsException) {
        }

        return 'dev';
    }

    private function getHeaders(string $contentType): array
    {
        return [
            'Content-Type: application/' . $contentType,
            'X-BI-API-KEY: ' . $this->apiKey,
            // lets BatteryIncluded tell which SDK versions are still in use
            'User-Agent: batteryincluded-php-sdk/' . self::getVersion() . ' php/' . PHP_VERSION,
        ];
    }
}
