<?php

declare(strict_types=1);

namespace Sportlog\GarminConnect\Queries;

/**
 * Represents a Garmin Connect API query.
 */
readonly class GarminConnectQuery
{
    /**
     * Constructor.
     * 
     * @param string $method HTTP method (e.g., GET, POST).
     * @param string $url The endpoint URL.
     * @param array $params Query parameters.
     */
    public function __construct(
        private string $method,
        private string $url,
        private array $params = []
    ) {}

    /**
     * Create a GET query.
     */
    public static function get(string $url, array $params = []): self
    {
        return new self('GET', $url, $params);
    }

    /** 
     * Get the HTTP method of the query.
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /** 
     * Get the URL of the query.
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /** 
     * Get the query parameters.
     */
    public function getQueryParams(): array
    {
        return $this->params;
    }
}
