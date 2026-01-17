<?php

declare(strict_types=1);

namespace Sportlog\GarminConnect;

use InvalidArgumentException;
use JsonSerializable;

/**
 * OAuth2Token class
 */
class OAuth2Token implements JsonSerializable
{
    private array $data;

    /**
     * OAuth2Token constructor.
     *
     * @param array $data The token data.
     * @throws InvalidArgumentException if required data is missing.
     */
    public function __construct(array $data)
    {
        $key = ['scope', 'jti', 'token_type', 'access_token', 'refresh_token', 'expires_in', 'refresh_token_expires_in'];
        $missingKeys = array_filter($key, fn($k) => !isset($data[$k]) || empty($data[$k]));
        if (!empty($missingKeys)) {
            throw new InvalidArgumentException("Missing OAuth2 token data: " . implode(', ', $missingKeys));
        }

        if (!isset($data['expires_at']) || !isset($data['refresh_token_expires_at'])) {
            $time = time();

            $this->data = array_merge($data, [
                'expires_at' => $time + $data['expires_in'],
                'refresh_token_expires_at' => $time + $data['refresh_token_expires_in'],
            ]);
        } else {
            $this->data = $data;
        }
    }

    /**
     * Creates an OAuth2Token from a JSON string.
     *
     * @param string $json The JSON string containing the token data.
     * @return OAuth2Token
     * @throws InvalidArgumentException if the JSON is invalid.
     */
    public static function fromJson(string $json): self
    {
        $data = json_decode($json, true);
        if (!is_array($data)) {
            throw new InvalidArgumentException("Invalid JSON data for OAuth2 token");
        }

        return new self($data);
    }

    public function getAccessToken(): string
    {
        return $this->data['access_token'];
    }

    public function getExpiresIn(): int
    {
        return $this->data['expires_in'];
    }

    public function getRefreshTokenExpiresIn(): int
    {
        return $this->data['refresh_token_expires_in'];
    }

    public function getRefreshToken(): string
    {
        return $this->data['refresh_token'];
    }

    public function getTokenType(): string
    {
        return $this->data['token_type'];
    }

    public function getJti(): string
    {
        return $this->data['jti'];
    }

    public function getScope(): string
    {
        return $this->data['scope'];
    }

    public function getExpiresAt(): int
    {
        return $this->data['expires_at'];
    }

    public function getRefreshTokenExpiresAt(): int
    {
        return $this->data['refresh_token_expires_at'];
    }

    public function isValid(): bool
    {
        return time() < $this->getExpiresAt();
    }

    public function isRefreshTokenValid(): bool
    {
        return time() < $this->getRefreshTokenExpiresAt();
    }

    public function jsonSerialize(): array
    {
        return $this->data;
    }
}
