<?php

declare(strict_types=1);

namespace BatteryIncludedSdk\Service;

class Response
{
    public function __construct(private string $responseRaw, private int $statusCode)
    {
    }

    public function getBody(): array
    {
        $responseRaw = $this->getRawResponse();
        if (!$responseRaw || $this->getStatusCode() === 404) {
            return [];
        }

        return json_decode($responseRaw, true);
    }

    public function getRawResponse(): string
    {
        return $this->responseRaw;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}
