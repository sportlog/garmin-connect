<?php

declare(strict_types=1);

namespace Sportlog\GarminConnect;

use Sportlog\GarminConnect\Queries\GarminConnectQuery;

/**
 * Garmin Connect API access.
 */
interface GarminConnectApiInterface
{
    /**
     * Runs a query against the Garmin Connect API.
     * Use the GarminConnectQueryFactory for pre-built queries.
     * 
     * @param GarminConnectQuery $query The query to run.
     * @return HttpResponse The HTTP response.
     */
    public function runQuery(GarminConnectQuery $query): HttpResponse;
}
