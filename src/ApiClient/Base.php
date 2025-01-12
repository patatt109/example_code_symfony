<?php

declare(strict_types=1);

namespace App\ApiClient;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\ResponseInterface;

abstract class Base
{
    private string $baseUrl;

    public function __construct(
        private readonly Client $httpClient
    ) {}

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    public function setBaseUrl(string $baseUrl): void
    {
        $this->baseUrl = rtrim($baseUrl, '/');
    }

    public function getHttpClient(): Client
    {
        return $this->httpClient;
    }

    /**
     * @throws GuzzleException
     */
    protected function call(string $httpMethod, string $apiMethod, array $options = []): ResponseInterface
    {
        $apiMethod = ltrim($apiMethod, '/');
        return $this->httpClient->send(new Request($httpMethod, "$this->baseUrl/$apiMethod"), $options);
    }
}
