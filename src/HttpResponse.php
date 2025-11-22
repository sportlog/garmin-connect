<?php

declare(strict_types=1);

namespace Sportlog\GarminConnect;

use Stringable;

/**
 * HTTP Response.
 */
readonly class HttpResponse implements Stringable
{
    /**
     * Constructor.
     * 
     * @param string $url The requested URL.
     * @param int $statusCode The HTTP response code.
     * @param string|null $body The response body.
     */
    public function __construct(public string $url, public int $statusCode, public ?string $body) {}

    /**
     * Checks if the response was successful (HTTP 200).
     */
    public function isSuccess(): bool
    {
        return $this->statusCode === 200;
    }

    /**
     * Returns the response or empty if there is no response.
     */
    public function __toString(): string
    {
        return $this->body ?? '';
    }

    /**
     * Decodes the response body as JSON.
     * 
     * @return array|object|null The decoded JSON, or null if the body is null or body could not be decoded.
     */
    public function toJson(): array|object|null
    {
        return !is_null($this->body) ? json_decode($this->body) : null;
    }
}
