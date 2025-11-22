<?php

declare(strict_types=1);

namespace Sportlog\GarminConnect;

use InvalidArgumentException;
use JsonSerializable;

/**
 * OAuth1Token class
 */
class OAuth1Token implements JsonSerializable
{
    /**
     * OAuth1Token constructor.
     *
     * @param string $oauthToken
     * @param string $oauthTokenSecret
     * @throws InvalidArgumentException if required data is missing.
     */
    public function __construct(private string $oauthToken, private string $oauthTokenSecret)
    {
        if (empty($oauthToken)) {
            throw new InvalidArgumentException("did not receive valid OAuth1 token");
        }
        if (empty($oauthTokenSecret)) {
            throw new InvalidArgumentException("did not receive valid OAuth1 token secret");
        }
    }

    /**
     * Creates an OAuth1Token from an associative array.
     *
     * @param array $data The array containing the token data.
     * @return OAuth1Token
     * @throws InvalidArgumentException if required data is missing.
     */
    public static function fromArray(array $data): self
    {
        if (empty($data['oauth_token']) || empty($data['oauth_token_secret'])) {
            throw new InvalidArgumentException("did not receive valid OAuth1 token");
        }

        return new self($data['oauth_token'], $data['oauth_token_secret']);
    }

    /**
     * Creates an OAuth1Token from a JSON string.
     *
     * @param string $json The JSON string containing the token data.
     * @return OAuth1Token
     * @throws InvalidArgumentException if the JSON is invalid.
     */
    public static function fromJson(string $json): self
    {
        $data = json_decode($json, true);
        if (!is_array($data)) {
            throw new InvalidArgumentException("Invalid JSON data for OAuth1 token");
        }

        return self::fromArray($data);
    }

    public function getToken(): string
    {
        return $this->oauthToken;
    }

    public function getTokenSecret(): string
    {
        return $this->oauthTokenSecret;
    }

    public function jsonSerialize(): array
    {
        return [
            'oauth_token' => $this->oauthToken,
            'oauth_token_secret' => $this->oauthTokenSecret,
        ];
    }
}
