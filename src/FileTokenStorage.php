<?php

declare(strict_types=1);

namespace Sportlog\GarminConnect;

use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * FileTokenStorage class
 */
class FileTokenStorage implements TokenStorageInterface
{
    private string $oauth1TokenFilename = 'oauth1_token_%s.json';
    private string $oauth2TokenFilename = 'oauth2_token_%s.json';

    /**
     * Constructor.
     * 
     * @param string $path The full path where the token will be stored. Must be writable.
     * @param LoggerInterface $logger Logger instance for logging.
     */
    public function __construct(private readonly string $path, private readonly LoggerInterface $logger = new NullLogger())
    {
        if (!is_dir($this->path)) {
            throw new InvalidArgumentException("The provided path is not a directory: {$this->path}");
        }
    }

    /* @inheritdoc */
    public function saveOAuth1Token(string $id, OAuth1Token $token): void
    {
        $this->writeAsJson($this->oauth1TokenFilename, $id, $token);
    }

    /* @inheritdoc */
    public function loadOAuth1Token(string $id): ?OAuth1Token
    {
        $data = $this->loadFile($this->oauth1TokenFilename, $id);
        return !is_null($data) ? OAuth1Token::fromJson($data) : null;
    }

    /* @inheritdoc */
    public function saveOAuth2Token(string $id, OAuth2Token $token): void
    {
        $this->writeAsJson($this->oauth2TokenFilename, $id, $token);
    }

    /* @inheritdoc */
    public function loadOAuth2Token(string $id): ?OAuth2Token
    {
        $data = $this->loadFile($this->oauth2TokenFilename, $id);
        return !is_null($data) ? OAuth2Token::fromJson($data) : null;
    }

    private function writeAsJson(string $filename, string $id, OAuth1Token | OAuth2Token $token): void
    {
        $fullFilename = $this->getFilename($filename, $id);
        if (file_put_contents($fullFilename, json_encode($token, JSON_PRETTY_PRINT))) {
            $this->logger->info("Token saved to file storage", ['id' => $id, 'file' => $fullFilename]);
        } else {
            $this->logger->error("Could not save token to file storage", ['id' => $id, 'file' => $fullFilename]);
        }
    }

    private function loadFile(string $filename, string $id): ?string
    {
        $fullFilename = $this->getFilename($filename, $id);
        if (!file_exists($fullFilename)) {
            return null;
        }

        $data = file_get_contents($fullFilename);
        if ($data === false) {
            return null;
        }

        return $data;
    }

    private function getFilename(string $filename, string $id): string
    {
        $path = $this->path;
        if (!str_ends_with($path, DIRECTORY_SEPARATOR)) {
            $path .= DIRECTORY_SEPARATOR;
        }

        return $path . sprintf($filename, $id);
    }
}
