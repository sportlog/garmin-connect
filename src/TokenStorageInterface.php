<?php

declare(strict_types=1);

namespace Sportlog\GarminConnect;

/**
 * Interface for token storage.
 * OAuth1 and OAuth2 tokens can be saved and loaded using this interface.
 */
interface TokenStorageInterface
{
    /**
     * Saves the OAuth1 token.
     *
     * @param string $id The ID of the token to save.
     * @param OAuth1Token $token
     *
     * @return void
     */
    public function saveOAuth1Token(string $id, OAuth1Token $token): void;

    /**
     * Loads the OAuth1 token.
     *
     * @param string $id The ID of the token to load.
     * @return OAuth1Token|null
     */
    public function loadOAuth1Token(string $id): ?OAuth1Token;

    /**
     * Saves the OAuth2 token.
     *
     * @param string $id The ID of the token to save.
     * @param OAuth2Token $token
     *
     * @return void
     */
    public function saveOAuth2Token(string $id, OAuth2Token $token): void;

    /**
     * Loads the OAuth2 token.
     *
     * @param string $id The ID of the token to load.
     * @return OAuth2Token|null
     */
    public function loadOAuth2Token(string $id): ?OAuth2Token;
}
