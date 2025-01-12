<?php

declare(strict_types=1);

namespace App\ApiClient\SomeMessenger;

use App\ApiClient\Base;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message\ResponseInterface;

class ReceiptsBotClient extends Base
{
    private string $botId;
    private string $clientId;
    private string $dialogId;

    public function getBotId(): string
    {
        return $this->botId;
    }

    public function setBotId(string $botId): void
    {
        $this->botId = $botId;
    }

    public function getClientId(): string
    {
        return $this->clientId;
    }

    public function setClientId(string $clientId): void
    {
        $this->clientId = $clientId;
    }

    public function getDialogId(): string
    {
        return $this->dialogId;
    }

    public function setDialogId(string $dialogId): void
    {
        $this->dialogId = $dialogId;
    }

    /**
     * @throws GuzzleException
     * @throws \JsonException
     */
    public function sendMessage(string $message): array
    {
        $content = $this->getSendMessageResponse($message)->getBody()->getContents();
        return json_decode($content, true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * @throws GuzzleException
     */
    public function getSendMessageResponse(string $message): ResponseInterface
    {
        $query = [
            'bot_id' => $this->botId,
            'client_id' => $this->clientId,
            'dialog_id' => $this->dialogId,
            'message' => $message,
        ];
        return $this->get('bot.message.add.json', $query);
    }

    /**
     * @throws GuzzleException
     */
    public function get(string $apiMethod, array $requestData = []): ResponseInterface
    {
        $options = [];
        if (!empty($requestData)) {
            $options[RequestOptions::QUERY] = $requestData;
        }
        return $this->call('get', $apiMethod, $options);
    }
}