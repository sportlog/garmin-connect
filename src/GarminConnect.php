<?php

declare(strict_types=1);

namespace Sportlog\GarminConnect;

use OAuth;
use Exception;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Main class for interacting with Garmin Connect.
 * Handles authentication and running queries against the Garmin Connect API.
 * 
 * This implementation is a port of garth (@link https://github.com/matin/garth).
 * 
 * @package Sportlog\GarminConnect
 */
readonly class GarminConnect
{
    private readonly CurlRequestor $curlRequestor;
    private readonly string $id;

    /**
     * Constructor.
     * 
     * @param string $username The Garmin Connect username.
     * @param TokenStorageInterface $tokenStorage The token storage implementation.
     * @param LoggerInterface $logger Optional logger implementation. If null, a NullLogger will be used.
     */
    public function __construct(private string $username, private TokenStorageInterface $tokenStorage, private LoggerInterface $logger = new NullLogger())
    {
        $this->id = md5($username);
        $this->curlRequestor = new CurlRequestor($this->id);
    }

    /**
     * Logs in to Garmin Connect.
     * If multi-factor authentication is required, a ConnectResult with status MultiFactorAuthorizationRequired is returned
     * and a subsequent call to resumeLogin is necessary.
     * 
     * @param string $password The Garmin Connect password.
     */
    public function login(string $password): ConnectResult
    {
        $connectApi = $this->connectApi();
        if ($connectApi !== false) {
            $this->logger->info("Using valid OAuth2 token from storage");
            return ConnectResult::connected($connectApi);
        }

        // 1) set cookie
        $response = $this->curlRequestor->get(Url::build(Constants::GARMIN_SSO_EMBED, Constants::SSO_EMBED_PARAMS));
        $this->logResponse("setCookie", $response);
        if (!$response->isSuccess()) {
            throw new Exception("Failed to set cookie, HTTP code {$response->statusCode}");
        }

        // 2) Get login page
        $response = $this->curlRequestor->get(Url::build(Constants::GARMIN_SSO_SIGNIN, Constants::SSO_SIGNIN_PARAMS), ['Referer' => $response->url], true);
        $this->logResponse("getSigninPage", $response);
        if (!$response->isSuccess()) {
            throw new Exception("Failed to get signin page, HTTP code {$response->statusCode}");
        }

        $csrfToken = $this->getCsrfToken($response->body);
        if (empty($csrfToken)) {
            throw new Exception('Unable to find valid CSRF token');
        }
        $this->logger->info("CSRF-token found: {$csrfToken}");

        // 3) Signin
        $response = $this->signIn($csrfToken, $response->url, $this->username, $password);
        $this->logResponse("Signin", $response);
        if (!$response->isSuccess()) {
            if ($response->statusCode === 401) {
                throw new Exception("Invalid username or password");
            } else {
                throw new Exception("Failed to signin, HTTP code {$response->statusCode}");
            }
        }

        $title = $this->getTitle($response->body);
        if (!empty($title) && str_contains($title, "MFA")) {
            $csrfToken = $this->getCsrfToken($response->body);
            if (empty($csrfToken)) {
                throw new Exception('MFA required, but unable to find valid CSRF token');
            }

            $this->logger->info("MFA required, MFA token found: {$csrfToken}");
            return ConnectResult::mfa($csrfToken);
        }

        $oauth2token = $this->completeLogin($response->body);
        return ConnectResult::connected(new GarminConnectApi($this->id, $oauth2token, $this->logger));
    }

    /**
     * Resumes the login process after multi-factor authentication (MFA) is required.
     */
    public function resumeLogin(string $mfaCode, string $csrfToken): GarminConnectApiInterface
    {
        $id = $this->getId($this->username);
        $verify = $this->verify($mfaCode, $csrfToken);
        $oauth2token = $this->completeLogin($verify->body);
        return new GarminConnectApi($id, $oauth2token, $this->logger);
    }

    /**
     * Returns the GarminConnectApi if a valid OAuth2 token exists or the
     * token could be successfully refreshed.
     * Otherwise, returns false.
     */
    public function connectApi(): GarminConnectApi|false
    {
        $id = $this->getId($this->username);
        $token = $this->getToken($id);
        if ($token === null) {
            return false;
        }

        return new GarminConnectApi($id, $token, $this->logger);
    }

    private function completeLogin(string $response): OAuth2Token
    {
        $ticket = $this->getTicket($response);
        if (is_null($ticket)) {
            throw new Exception("No ticket found");
        }

        $oAuth1Token = $this->getOAuth1Token($ticket);
        $this->logger->info("oAuth1 token received");

        $oAuth2Token = $this->exchange($oAuth1Token);
        $this->logger->info("oAuth2 token received");

        return $oAuth2Token;
    }

    private function verify(string $mfaCode, string $csrfToken): HttpResponse
    {
        $this->logger->info("Calling verify for code {$mfaCode}");

        $verifyData = [
            "mfa-code" => $mfaCode,
            "embed" => "true",
            "_csrf" => $csrfToken,
            "fromPage" => "setupEnterMfaCode",
        ];

        $headers = [
            'Content-Type' => Constants::CONTENT_TYPE_FORM_URL_ENCODED,
            'Referer' => Url::build(Constants::GARMIN_SSO_SIGNIN, Constants::SSO_SIGNIN_PARAMS),
        ];

        $response = $this->curlRequestor->post(Url::build(Constants::GARMIN_SSO_VERIFY_MFA, Constants::SSO_SIGNIN_PARAMS), $verifyData, $headers, true);
        $this->logResponse("verifyMFA", $response);

        return $response;
    }

    private function signIn(string $csrfToken, string $referer, string $username, string $password): HttpResponse
    {
        $postData = [
            'username' => $username,
            'password' => $password,
            'embed' => 'true',
            '_csrf' => $csrfToken,
        ];

        $headers = [
            'Content-Type' => Constants::CONTENT_TYPE_FORM_URL_ENCODED,
            'Referer' => $referer
        ];

        return $this->curlRequestor->post(Url::build(Constants::GARMIN_SSO_SIGNIN, Constants::SSO_SIGNIN_PARAMS), $postData, $headers, true);
    }

    private function getOAuth1Token(string $ticket): OAuth1Token
    {
        $params = [
            'ticket' => $ticket,
            'login-url' => Constants::GARMIN_SSO_EMBED,
            'accepts-mfa-tokens' => true
        ];

        /** @disregard P1009 Undefined type */
        $oAuth = new OAuth(Constants::CONSUMER_KEY, Constants::CONSUMER_SECRET);
        $oauthHeader = $oAuth->getRequestHeader('GET', Constants::GARMIN_CONNECT_API_OAUTH_PREAUTHORIZED, $params);
        if ($oauthHeader === false) {
            throw new Exception("failed to generate OAuth1 signature");
        }

        $headers = [
            "Authorization" => $oauthHeader,
            "User-Agent" => Constants::USER_AGENT
        ];

        $response = $this->curlRequestor->get(Url::build(Constants::GARMIN_CONNECT_API_OAUTH_PREAUTHORIZED, $params), headers: $headers);
        $this->logResponse("getOAuth1Token", $response);
        if (!$response->isSuccess()) {
            throw new Exception("Failed to get OAuth1 token, HTTP code {$response->statusCode}");
        }

        $data = [];
        parse_str($response->body, $data);
        $oAuth1Token = OAuth1Token::fromArray($data);
        $this->tokenStorage->saveOAuth1Token($this->id, $oAuth1Token);

        return $oAuth1Token;
    }

    private function exchange(OAuth1Token $oAuth1Token): OAuth2Token

    {
        /** @disregard P1009 Undefined type */
        $oAuth = new OAuth(Constants::CONSUMER_KEY, Constants::CONSUMER_SECRET);
        $oAuth->setToken($oAuth1Token->getToken(), $oAuth1Token->getTokenSecret());

        $oauthHeader = $oAuth->getRequestHeader('POST', Constants::GARMIN_CONNECT_API_OAUTH_EXCHANGE_USER);
        if ($oauthHeader === false) {
            throw new Exception("failed to generate OAuth2 signature");
        }

        $headers = [
            'Authorization' => $oauthHeader,
            'User-Agent' => Constants::USER_AGENT,
            'Content-Type' => Constants::CONTENT_TYPE_FORM_URL_ENCODED
        ];

        $response = $this->curlRequestor->post(Constants::GARMIN_CONNECT_API_OAUTH_EXCHANGE_USER, [], $headers);
        $this->logResponse("OAuth2 token", $response);
        if (!$response->isSuccess()) {
            throw new Exception("Failed to get OAuth2 token, HTTP code {$response->statusCode}");
        }

        $token = OAuth2Token::fromJson($response->body);
        $this->tokenStorage->saveOAuth2Token($this->id, $token);
        return $token;
    }

    /**
     * Checks for a valid OAuth2 token in storage.
     * If the token is expired but the refresh token is still valid,
     * it will attempt to refresh the token.
     *
     * @return OAuth2Token|null
     */
    private function getToken(string $id): ?OAuth2Token
    {
        $token = $this->tokenStorage->loadOAuth2Token($id);
        if ($token === null) {
            return null;
        }

        if ($token->isValid()) {
            return $token;
        }

        if ($token->isRefreshTokenValid()) {
            $oAuth1Token = $this->tokenStorage->loadOAuth1Token($id);
            if (!is_null($oAuth1Token)) {
                return $this->exchange($oAuth1Token);
            }
        }

        return null;
    }

    private function getId(string $username): string
    {
        return md5($username);
    }

    private function logResponse(string $description, HttpResponse $response): void
    {
        $this->logger->info("{$description} returned HTTP {$response->statusCode}");
        $this->logger->info($response);
    }

    private function getCsrfToken(string $response): ?string
    {
        $matches = $this->findByRegex("/name=\"_csrf\"\s+value=\"(.*)\"/", $response);
        return isset($matches[1]) ? $matches[1] : null;
    }

    private function getTicket(string $response): ?string
    {
        $matches = $this->findByRegex("/embed\?ticket=([^\"]+)\"/", $response);
        return isset($matches[1]) ? $matches[1] : null;
    }

    private function getTitle(string $response): ?string
    {
        $matches = $this->findByRegex("/<title>(.*)<\/title>/", $response);
        return isset($matches[1]) ? $matches[1] : null;
    }

    /**
     * @return string[]|false
     */
    private function findByRegex(string $pattern, string $subject): array|false
    {
        $matches = [];
        return !preg_match($pattern, $subject, $matches) ? false : $matches;
    }
}
