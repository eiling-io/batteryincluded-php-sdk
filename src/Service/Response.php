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
        return json_decode($this->responseRaw, true);
    }

    public function getRawResponse(): string
    {
        return $this->responseRaw;
    }
}
