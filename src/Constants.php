<?php

declare(strict_types=1);

namespace Sportlog\GarminConnect;

interface Constants
{
    const GARMIN_CONNECT = "https://connect.garmin.com";
    const GARMIN_CONNECT_API_OAUTH = 'https://connectapi.garmin.com/oauth-service/oauth';
    const GARMIN_SSO = 'https://sso.garmin.com/sso';
    const GARMIN_SSO_SIGNIN = self::GARMIN_SSO . '/signin';
    const GARMIN_SSO_EMBED = self::GARMIN_SSO . '/embed';
    const GARMIN_SSO_VERIFY_MFA = self::GARMIN_SSO . '/verifyMFA/loginEnterMfaCode';
    const USER_AGENT = "com.garmin.android.apps.connectmobile";
    const CONTENT_TYPE_FORM_URL_ENCODED = 'application/x-www-form-urlencoded';
    const GARMIN_CONNECT_API_OAUTH_PREAUTHORIZED = self::GARMIN_CONNECT_API_OAUTH . '/preauthorized';
    const GARMIN_CONNECT_API_OAUTH_EXCHANGE_USER = self::GARMIN_CONNECT_API_OAUTH . '/exchange/user/2.0';

    /**
     * Consumer key and secret for OAuth1 authentication.
     * https://thegarth.s3.amazonaws.com/oauth_consumer.json
     */
    const CONSUMER_KEY = "fc3e99d2-118c-44b8-8ae3-03370dde24c0";
    const CONSUMER_SECRET = "E08WAR897WEy2knn7aFBrvegVAf0AFdWBBF";

    const SSO_EMBED_PARAMS = [
        'id' => "gauth-widget",
        'embedWidget' => "true",
        'gauthHost' => self::GARMIN_SSO
    ];

    const SSO_SIGNIN_PARAMS = self::SSO_EMBED_PARAMS + [
        'gauthHost' => self::GARMIN_SSO_EMBED,
        'service' => self::GARMIN_SSO_EMBED,
        'source' => self::GARMIN_SSO_EMBED,
        'redirectAfterAccountLoginUrl' => self::GARMIN_SSO_EMBED,
        'redirectAfterAccountCreationUrl' => self::GARMIN_SSO_EMBED
    ];
}
