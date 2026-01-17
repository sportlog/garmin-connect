<?php

declare(strict_types=1);

/**
 * Sportlog (https://sportlog.at)
 *
 * @license MIT License
 */

namespace Sportlog\GarminConnect\Test\TestCase;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Sportlog\GarminConnect\OAuth1Token;

class OAuth1TokenTest extends TestCase
{
    public function testConstructorWithValidToken(): void
    {
        $token = new OAuth1Token('test_token', 'test_secret');

        $this->assertSame('test_token', $token->getToken());
        $this->assertSame('test_secret', $token->getTokenSecret());
    }

    public function testConstructorWithEmptyTokenThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("did not receive valid OAuth1 token");

        new OAuth1Token('', 'test_secret');
    }

    public function testConstructorWithEmptySecretThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("did not receive valid OAuth1 token secret");

        new OAuth1Token('test_token', '');
    }

    public function testFromArrayWithValidData(): void
    {
        $data = [
            'oauth_token' => 'token_value',
            'oauth_token_secret' => 'secret_value',
        ];

        $token = OAuth1Token::fromArray($data);

        $this->assertInstanceOf(OAuth1Token::class, $token);
        $this->assertSame('token_value', $token->getToken());
        $this->assertSame('secret_value', $token->getTokenSecret());
    }

    public function testFromArrayWithMissingTokenThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("did not receive valid OAuth1 token");

        OAuth1Token::fromArray([
            'oauth_token_secret' => 'secret_value',
        ]);
    }

    public function testFromArrayWithMissingSecretThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("did not receive valid OAuth1 token");

        OAuth1Token::fromArray([
            'oauth_token' => 'token_value',
        ]);
    }

    public function testFromArrayWithEmptyTokenThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("did not receive valid OAuth1 token");

        OAuth1Token::fromArray([
            'oauth_token' => '',
            'oauth_token_secret' => 'secret_value',
        ]);
    }

    public function testFromArrayWithEmptySecretThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("did not receive valid OAuth1 token");

        OAuth1Token::fromArray([
            'oauth_token' => 'token_value',
            'oauth_token_secret' => '',
        ]);
    }

    public function testFromJsonWithValidJson(): void
    {
        $json = json_encode([
            'oauth_token' => 'json_token',
            'oauth_token_secret' => 'json_secret',
        ]);

        $token = OAuth1Token::fromJson($json);

        $this->assertInstanceOf(OAuth1Token::class, $token);
        $this->assertSame('json_token', $token->getToken());
        $this->assertSame('json_secret', $token->getTokenSecret());
    }

    public function testFromJsonWithInvalidJsonThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid JSON data for OAuth1 token");

        OAuth1Token::fromJson('not valid json');
    }

    public function testFromJsonWithMissingDataThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("did not receive valid OAuth1 token");

        $json = json_encode([
            'oauth_token' => 'token_value',
        ]);

        OAuth1Token::fromJson($json);
    }

    public function testJsonSerialize(): void
    {
        $token = new OAuth1Token('serialize_token', 'serialize_secret');

        $json = json_encode($token);
        $data = json_decode($json, true);

        $this->assertSame('serialize_token', $data['oauth_token']);
        $this->assertSame('serialize_secret', $data['oauth_token_secret']);
    }

    public function testJsonSerializeAndDeserialize(): void
    {
        $original = new OAuth1Token('roundtrip_token', 'roundtrip_secret');
        $json = json_encode($original);

        $restored = OAuth1Token::fromJson($json);

        $this->assertSame($original->getToken(), $restored->getToken());
        $this->assertSame($original->getTokenSecret(), $restored->getTokenSecret());
    }
}
