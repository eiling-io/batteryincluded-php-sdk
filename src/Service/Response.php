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
        $responseRaw = $this->getRawResponse();
        if (!$responseRaw) {
            return [];
        }

        return json_decode($responseRaw, true);
    }

    public function getRawResponse(): string
    {
        return $this->responseRaw;
    }
}
