<?php

declare(strict_types=1);

namespace App\Services;

use App\ApiClient\SomeMessenger\ReceiptsBotClient as SomeMessengerReceiptsBotClient;
use App\Entity\Receipts\BankTwoPayment;
use App\Repository\Receipts\BankTwoPaymentRepository;
use DateMalformedStringException;
use DateTimeImmutable;
use DateTimeZone;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use BankTwo\Client as BankTwoClient;
use BankTwo\Common\Exceptions\ApiException;
use BankTwo\Common\Exceptions\BadApiRequestException;
use BankTwo\Common\Exceptions\ExtensionNotFoundException;
use BankTwo\Common\Exceptions\ForbiddenException;
use BankTwo\Common\Exceptions\InternalServerError;
use BankTwo\Common\Exceptions\NotFoundException;
use BankTwo\Common\Exceptions\ResponseProcessingException;
use BankTwo\Common\Exceptions\TooManyRequestsException;
use BankTwo\Common\Exceptions\UnauthorizedException;
use BankTwo\Model\Payment\PaymentStatus;

readonly class ReceiptsBankTwo
{
    public function __construct(
        private SomeMessengerReceiptsBotClient $receiptsBotClient,
        private BankTwoClient $bankTwoApiClient,
        private BankTwoPaymentRepository $bankTwoPaymentRepository
    ) {}

    /**
     * @throws ApiException
     * @throws BadApiRequestException
     * @throws ExtensionNotFoundException
     * @throws ForbiddenException
     * @throws InternalServerError
     * @throws NotFoundException
     * @throws ResponseProcessingException
     * @throws TooManyRequestsException
     * @throws UnauthorizedException
     * @throws DateMalformedStringException
     * @throws Exception
     */
    public function load(): void
    {
        $payments = $this->getLatestPayments();
        $payments = array_reverse($payments);
        foreach ($payments as $payment) {
            $entity = $this->bankTwoPaymentRepository->findOneByCriteriaOrNew([
                'paymentId' => $payment->getId(),
            ]);
            if ($entity->getId() === null) {
                $entity = $this->bankTwoPaymentRepository->populateByApiModel($entity, $payment);
                $this->bankTwoPaymentRepository->add($entity);
            }
        }
        $this->bankTwoPaymentRepository->save();
    }

    /**
     * @throws GuzzleException
     * @throws DateMalformedStringException
     * @throws \JsonException
     */
    public function publish(): void
    {
        /** @var BankTwoPayment[] $unpublished */
        $unpublished = $this->bankTwoPaymentRepository->findUnpublished();
        foreach ($unpublished as $entity) {
            $message = $this->createMessage($entity);
            $result = $this->receiptsBotClient->sendMessage($message);
            if (is_int($result['result']) && $result['result'] > 0) {
                $entity->setBxMessageId($result['result']);
                $entity->setPublishedAt(new DateTimeImmutable($result['time']['date_finish']));
                $this->bankTwoPaymentRepository->save();
                sleep(1);
            }
        }
    }

    public function createMessage(BankTwoPayment $payment): string
    {
        $amount = $payment->getAmount() / 100;
        $currency = $payment->getCurrency() ?? '';
        $description = $payment->getDescription() ?? '';
        return <<<EOT
Банк 2
Входящий платёж на {$amount} {$currency}
{$description}
Идентификатор: {$payment->getPaymentId()}
EOT;
    }

    /**
     * @throws ApiException
     * @throws BadApiRequestException
     * @throws ExtensionNotFoundException
     * @throws ForbiddenException
     * @throws InternalServerError
     * @throws NotFoundException
     * @throws ResponseProcessingException
     * @throws TooManyRequestsException
     * @throws UnauthorizedException
     * @throws DateMalformedStringException
     */
    public function getLatestPayments(): array
    {
        $latestPayments = [];
        $cursor = null;
        $params = [
            'limit' => 100,
            'status' => PaymentStatus::SUCCEEDED,
            'captured_at.gte' => (new DateTimeImmutable('-3 days midnight', new DateTimeZone('Europe/Moscow')))
                ->setTimezone(new DateTimeZone('UTC'))
                ->format('Y-m-d\TH:i:s.vp'),
            'captured_at.lte' => (new DateTimeImmutable('now', new DateTimeZone('Europe/Moscow')))
                ->setTimezone(new DateTimeZone('UTC'))
                ->format('Y-m-d\TH:i:s.vp'),
        ];
        do {
            $params['cursor'] = $cursor;
            $paymentsResponse = $this->bankTwoApiClient->getPayments($params);
            if (!$paymentsResponse) {
                throw new ApiException('Payments not found');
            }
            $latestPayments = [...$latestPayments, ...$paymentsResponse->getItems()];
        } while ($cursor = $paymentsResponse->getNextCursor());
        return $latestPayments;
    }
}