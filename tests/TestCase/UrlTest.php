<?php

declare(strict_types=1);

namespace Sportlog\GarminConnect\Test\TestCase;

use PHPUnit\Framework\TestCase;
use Sportlog\GarminConnect\Url;

class UrlTestCase extends TestCase
{
    public function testBuildUrlWithoutParams(): void
    {
        $url = Url::build('https://example.com/api/resource', []);
        $this->assertEquals('https://example.com/api/resource', $url);
    }

    public function testBuildUrlWithParams(): void
    {
        $url = Url::build('https://example.com/api/resource', ['key1' => 'value1', 'key2' => 'value2']);
        $this->assertEquals('https://example.com/api/resource?key1=value1&key2=value2', $url);
    }

    public function testBuildUrlWithArrayInput(): void
    {
        $url = Url::build(['https://example.com', 'api', 'resource'], ['key' => 'value']);
        $this->assertEquals('https://example.com/api/resource?key=value', $url);
    }

    public function testBuildUrlWithArrayWithSlashInput(): void
    {
        $url = Url::build(['https://example.com/', 'api/', 'resource'], ['key' => 'value']);
        $this->assertEquals('https://example.com/api/resource?key=value', $url);
    }

    public function testBuildUrlWithEmptyParamsInput(): void
    {
        $url = Url::build('https://example.com/api/resource', ['key1' => 'value1', 'key2' => null, 'key3' => '']);
        $this->assertEquals('https://example.com/api/resource?key1=value1', $url);
    }
}
