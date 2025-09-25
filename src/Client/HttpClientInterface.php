<?php

declare(strict_types=1);

namespace BatteryIncludedSdk\Client;

use BatteryIncludedSdk\Service\Response;

interface HttpClientInterface
{
    public function send(string $url, string $method, array $header, string $data): Response;
}
