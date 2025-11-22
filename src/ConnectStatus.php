<?php

declare(strict_types=1);

namespace Sportlog\GarminConnect;

/**
 * Status of the connection.
 */
enum ConnectStatus: string
{
    case Connected = 'S';
    case MultiFactorAuthorizationRequired = 'MFA';
}
