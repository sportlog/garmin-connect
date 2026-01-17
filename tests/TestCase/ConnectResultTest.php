<?php

declare(strict_types=1);

namespace Sportlog\GarminConnect\Test\TestCase;

use PHPUnit\Framework\TestCase;
use Sportlog\GarminConnect\ConnectResult;
use Sportlog\GarminConnect\ConnectStatus;
use Sportlog\GarminConnect\GarminConnectApiInterface;
use Sportlog\GarminConnect\HttpResponse;
use Sportlog\GarminConnect\Queries\GarminConnectQuery;

class ConnectResultTest extends TestCase
{
    public function testConnectedReturnsConnectedStatusAndApiInstance(): void
    {
        $api = $this->createApiStub();

        $result = ConnectResult::connected($api);

        $this->assertSame(ConnectStatus::Connected, $result->getStatus());
        $this->assertSame($api, $result->connectApi);
        $this->assertNull($result->csrfToken);
    }

    public function testMfaReturnsMfaStatusAndCsrfToken(): void
    {
        $result = ConnectResult::mfa('csrf-token');

        $this->assertSame(ConnectStatus::MultiFactorAuthorizationRequired, $result->getStatus());
        $this->assertSame('csrf-token', $result->csrfToken);
        $this->assertNull($result->connectApi);
    }

    private function createApiStub(): GarminConnectApiInterface
    {
        return new class implements GarminConnectApiInterface {
            public function runQuery(GarminConnectQuery $query): HttpResponse
            {
                return new HttpResponse($query->getUrl(), 200, null);
            }
        };
    }
}
