<?php

declare(strict_types=1);

namespace Sportlog\GarminConnect\Test\TestCase;

use PHPUnit\Framework\TestCase;
use Sportlog\GarminConnect\FileTokenStorage;
use Sportlog\GarminConnect\OAuth1Token;
use Sportlog\GarminConnect\OAuth2Token;

class FileTokenStorageTest extends TestCase
{
    public function testSaveAndLoadOAuth1Token(): void
    {
        $dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'garmin_test_' . uniqid();
        mkdir($dir);

        $storage = new FileTokenStorage($dir);

        $token = new OAuth1Token('my_token', 'my_secret');
        $storage->saveOAuth1Token('user1', $token);

        $loaded = $storage->loadOAuth1Token('user1');
        $this->assertInstanceOf(OAuth1Token::class, $loaded);
        $this->assertSame('my_token', $loaded->getToken());
        $this->assertSame('my_secret', $loaded->getTokenSecret());

        // cleanup
        foreach (glob($dir . DIRECTORY_SEPARATOR . '*') as $f) {
            @unlink($f);
        }
        rmdir($dir);
    }

    public function testSaveAndLoadOAuth2Token(): void
    {
        $dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'garmin_test_' . uniqid();
        mkdir($dir);

        $storage = new FileTokenStorage($dir);

        $data = [
            'scope' => 'read',
            'jti' => 'jti-1',
            'token_type' => 'bearer',
            'access_token' => 'access-1',
            'refresh_token' => 'refresh-1',
            'expires_in' => 3600,
            'refresh_token_expires_in' => 7200,
        ];

        $token = new OAuth2Token($data);
        $storage->saveOAuth2Token('user2', $token);

        $loaded = $storage->loadOAuth2Token('user2');
        $this->assertInstanceOf(OAuth2Token::class, $loaded);
        $this->assertSame('access-1', $loaded->getAccessToken());
        $this->assertSame('refresh-1', $loaded->getRefreshToken());

        // cleanup
        foreach (glob($dir . DIRECTORY_SEPARATOR . '*') as $f) {
            @unlink($f);
        }
        rmdir($dir);
    }

    public function testLoadingMissingReturnsNull(): void
    {
        $dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'garmin_test_' . uniqid();
        mkdir($dir);

        $storage = new FileTokenStorage($dir);

        $this->assertNull($storage->loadOAuth1Token('nope'));
        $this->assertNull($storage->loadOAuth2Token('nope'));

        rmdir($dir);
    }
}
