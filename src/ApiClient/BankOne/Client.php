<?php

declare(strict_types=1);

namespace App\ApiClient\BankOne;

use App\ApiClient\Base;
use DateTimeImmutable;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;

class Client extends Base
{
    private string $jwt;

    public function setJWT(string $jwt): void
    {
        $this->jwt = $jwt;
    }

    /**
     * @throws GuzzleException
     * @throws \JsonException
     */
    public function initStatement(
        string $accountId,
        DateTimeImmutable $startDateTime,
        DateTimeImmutable $endDateTime
    ): array {
        $content = $this->getInitStatementResponse($accountId, $startDateTime, $endDateTime)->getBody()->getContents();
        $json = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        if (isset($json['code']) && ((int)$json['code']) >= 400) {
            throw new RuntimeException($json['message']);
        }
        return $json['Data']['Statement'];
    }

    /**
     * @throws GuzzleException
     */
    public function getInitStatementResponse(
        string $accountId,
        DateTimeImmutable $startDateTime,
        DateTimeImmutable $endDateTime
    ): ResponseInterface {
        $postData = [
            'Data' => [
                'Statement' => [
                    'accountId' => $accountId,
                    'startDateTime' => $startDateTime->format('Y-m-d'),
                    'endDateTime' => $endDateTime->format('Y-m-d'),
                ],
            ],
        ];
        return $this->post("open-banking/v1.0/statements", $postData);
    }

    /**
     * @throws GuzzleException
     * @throws \JsonException
     */
    public function getStatementTransactionList(string $accountId, string $statementId): array
    {
        $statement = $this->getStatement($accountId, $statementId);
        return $statement['Transaction'];
    }

    /**
     * @throws GuzzleException
     * @throws \JsonException
     */
    public function getStatement(string $accountId, string $statementId): array
    {
        $content = $this->getStatementResponse($accountId, $statementId)->getBody()->getContents();
        $json = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        if (isset($json['code']) && ((int)$json['code']) >= 400) {
            throw new RuntimeException($json['message']);
        }
        return $json['Data']['Statement'][0];
    }

    /**
     * @throws GuzzleException
     */
    public function getStatementResponse(string $accountId, string $statementId): ResponseInterface
    {
        return $this->get("open-banking/v1.0/accounts/$accountId/statements/$statementId");
    }

    /**
     * @throws GuzzleException
     */
    public function get(string $apiMethod, array $queryData = []): ResponseInterface
    {
        $options = [RequestOptions::HEADERS => $this->getHeaders()];
        if (!empty($queryData)) {
            $options[RequestOptions::QUERY] = $queryData;
        }
        return $this->call('get', $apiMethod, $options);
    }

    /**
     * @throws GuzzleException
     */
    public function post(string $apiMethod, array $postData = []): ResponseInterface
    {
        $options = [RequestOptions::HEADERS => $this->getHeaders()];
        if (!empty($postData)) {
            $options[RequestOptions::JSON] = $postData;
        }
        return $this->call('post', $apiMethod, $options);
    }

    private function getHeaders(): array
    {
        return [
            'Authorization' => "Bearer $this->jwt",
            'Accept' => 'application/json',
        ];
    }
}