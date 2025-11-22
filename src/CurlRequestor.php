<?php

declare(strict_types=1);

namespace Sportlog\GarminConnect;

use CurlHandle;
use Exception;

/**
 * CurlRequestor class for making HTTP requests using cURL.
 */
class CurlRequestor
{
    private const USER_AGENT = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/117.0.0.0 Safari/537.36';

    /**
     * Constructor.
     * 
     * @param string $cookieId The cookie id to use for this requestor.
     */
    public function __construct(private readonly string $cookieId) {}

    /**
     * Performs a GET request.
     * 
     * @param string $url The URL to request.
     * @param array $headers Optional headers to include in the request.
     * @param bool $useCookie Whether to use cookies for the request.
     * @return HttpResponse The HTTP response.
     */
    public function get(string $url, array $headers = [], bool $useCookie = false): HttpResponse
    {
        $handle = $this->createHandle($url, $headers, $useCookie);
        return $this->execute($handle, $url);
    }

    /**
     * Performs a POST request.
     * 
     * @param string $url The URL to request.
     * @param array $postData The data to post.
     * @param array $headers Optional headers to include in the request.
     * @param bool $useCookie Whether to use cookies for the request.
     * @return HttpResponse The HTTP response.
     */
    public function post(string $url, array $postData, array $headers = [], bool $useCookie = false): HttpResponse
    {
        $handle = $this->createHandle($url, $headers, $useCookie);

        curl_setopt($handle, CURLINFO_HEADER_OUT, true);
        curl_setopt($handle, CURLOPT_POST, true);

        if (!empty($postData)) {
            curl_setopt($handle, CURLOPT_POSTFIELDS, http_build_query($postData));
        }

        return self::execute($handle, $url);
    }

    private function createHandle(string $url, array $headers = [], bool $useCookie = false): CurlHandle
    {
        $handle = curl_init();
        if ($handle === false) {
            throw new Exception('Could not create handle');
        }

        $cookieFile = join(DIRECTORY_SEPARATOR, [sys_get_temp_dir(), "cookie_{$this->cookieId}.txt"]);
        curl_setopt($handle, CURLOPT_URL, $url);
        curl_setopt($handle, CURLOPT_COOKIEJAR, $cookieFile);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($handle, CURLOPT_TIMEOUT, 10);
        curl_setopt($handle, CURLOPT_USERAGENT, self::USER_AGENT);
        curl_setopt($handle, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($handle, CURLOPT_VERBOSE, true);

        if (!empty($headers)) {
            if (isset($headers['Referer'])) {
                curl_setopt($handle, CURLOPT_REFERER, $headers['Referer']);
            }

            $curlHeaders = [];
            foreach ($headers as $key => $value) {
                $curlHeaders[] = sprintf('%s: %s', $key, $value);
            }

            curl_setopt($handle, CURLOPT_HTTPHEADER, $curlHeaders);
        }

        if ($useCookie) {
            curl_setopt($handle, CURLOPT_COOKIEFILE, $cookieFile);
        }

        return $handle;
    }

    /**
     * Executes the curl request and returns the response.
     */
    private function execute(CurlHandle $handle, string $url): HttpResponse
    {
        $response = curl_exec($handle);
        $statusCode = (int)curl_getinfo($handle, CURLINFO_RESPONSE_CODE);

        curl_close($handle);

        if ($response === true) {
            throw new Exception('Curl Option "CURLOPT_RETURNTRANSFER" must be set to true');
        }

        return new HttpResponse($url, $statusCode, $response !== false ? $response : null);
    }
}
