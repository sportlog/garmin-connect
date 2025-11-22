<?php

declare(strict_types=1);

namespace Sportlog\GarminConnect;

/**
 * Result of a connection attempt.
 */
class ConnectResult
{
    /**
     * CSRF-Token used for multi-factor authorization.
     * Only set if @see ConnectStatus::MultiFactorAuthorizationRequired is returned.
     */
    public ?string $csrfToken = null;
    public ?GarminConnectApiInterface $connectApi = null;

    private function __construct(public ConnectStatus $status) {}

    /**
     * Creates a ConnectResult indicating a successful connection.
     */
    public static function connected(GarminConnectApiInterface $connectApi): self
    {
        $result = new self(ConnectStatus::Connected);
        $result->connectApi = $connectApi;
        return $result;
    }

    /**
     * Creates a ConnectResult indicating that multi-factor authorization is required.
     */
    public static function mfa(string $csrfToken): self
    {
        $result = new self(ConnectStatus::MultiFactorAuthorizationRequired);
        $result->csrfToken = $csrfToken;
        return $result;
    }

    /**
     * Returns the connection status.
     */
    public function getStatus(): ConnectStatus
    {
        return $this->status;
    }
}
