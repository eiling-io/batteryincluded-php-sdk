<?php

declare(strict_types=1);

namespace BatteryIncludedSdk\Service;

class Response
{
    public function __construct(private string $responseRaw)
    {
    }

    public function getBody(): array
    {
        $response = json_decode($this->responseRaw, true);
        if (!is_array($response)) {
            return [];
        }
        return $response;
    }

    public function getRawResponse(): string
    {
        return $this->responseRaw;
    }
}
