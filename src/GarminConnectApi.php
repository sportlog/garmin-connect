<?php

declare(strict_types=1);

namespace Sportlog\GarminConnect;

use Exception;
use Psr\Log\LoggerInterface;
use Sportlog\GarminConnect\Queries\GarminConnectQuery;

/**
 * Garmin Connect API access.
 */
class GarminConnectApi implements GarminConnectApiInterface
{
    private readonly CurlRequestor $curlRequestor;

    /**
     * Contructor.
     *  
     * @param string $id The cookie id for the user.
     * @param OAuth2Token $token The OAuth2 token for authentication.
     * @param LoggerInterface $logger Logger for logging messages.
     */
    public function __construct(private readonly string $id, private readonly OAuth2Token $token, private readonly LoggerInterface $logger)
    {
        $this->curlRequestor = new CurlRequestor($this->id);
    }

    /** @inheritdoc */
    public function runQuery(GarminConnectQuery $query): HttpResponse
    {
        $url = Url::build([Constants::GARMIN_CONNECT, $query->getUrl()], $query->getQueryParams());
        $headers = [
            'Authorization' => "Bearer {$this->token->getAccessToken()}",
            'DI-Backend' => Constants::GARMIN_CONNECT,
        ];

        $response = $this->curlRequestor->get($url, $headers);
        $this->logger->info("Query to {$url} returned HTTP {$response->statusCode}");

        return $response;
    }
}
