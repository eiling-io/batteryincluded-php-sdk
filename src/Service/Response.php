<?php

declare(strict_types=1);

namespace BatteryIncludedSdk\Service;

class Response
{
    public function __construct(private string $responseRaw, private int $statusCode)
    {
    }

    public function isSuccessful(): bool
    {
        return $this->statusCode >= 200 && $this->statusCode < 300;
    }

    /**
     * Decoded body of a successful response, [] for errors, so the responses render empty instead of failing.
     */
    public function getBody(): array
    {
        if (!$this->isSuccessful()) {
            return [];
        }

        $body = json_decode($this->getRawResponse(), true);

        return is_array($body) ? $body : [];
    }

    /**
     * Message of an error response, the raw body if the api did not send json.
     */
    public function getErrorMessage(): ?string
    {
        if ($this->isSuccessful()) {
            return null;
        }

        $body = json_decode($this->getRawResponse(), true);
        $message = is_array($body) ? ($body['message'] ?? $body['error'] ?? null) : null;

        return is_string($message) ? $message : $this->getRawResponse();
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
