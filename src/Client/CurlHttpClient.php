<?php

declare(strict_types=1);

namespace BatteryIncludedSdk\Client;

use BatteryIncludedSdk\Exception\NetworkException;
use BatteryIncludedSdk\Service\Response;

class CurlHttpClient implements HttpClientInterface
{
    /**
     * Without a timeout a hanging api blocks every page with a search until the php timeout. Imports are slow on the
     * other hand (720 products with images take about two minutes), so reads and writes have their own timeout.
     *
     * @param float $readTimeout    seconds for a GET request (search, suggestions, recommendations), 0 for no limit
     * @param float $connectTimeout seconds to establish the connection, 0 for no limit
     * @param float $writeTimeout   seconds for an import, update or delete, 0 for no limit
     */
    public function __construct(
        private float $readTimeout = 10.0,
        private float $connectTimeout = 5.0,
        private float $writeTimeout = 0.0,
    ) {
    }

    public function send(string $url, string $method, array $header, string $data): Response
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        switch ($method) {
            case 'GET':
                break;
            case 'POST':
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                break;
            case 'DELETE':
            case 'PATCH':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                break;
            default:
                throw new \InvalidArgumentException('Unsupported method: ' . $method);
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_TIMEOUT_MS, (int) (($method === 'GET' ? $this->readTimeout : $this->writeTimeout) * 1000));
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, (int) ($this->connectTimeout * 1000));
        // accepts every encoding curl supports, the responses of browse are large
        curl_setopt($ch, CURLOPT_ENCODING, '');

        $response = curl_exec($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($response === false || curl_errno($ch)) {
            throw new NetworkException(sprintf('%s %s failed: %s', $method, $url, curl_error($ch)), curl_errno($ch));
        }

        return new Response((string) $response, $statusCode);
    }
}
