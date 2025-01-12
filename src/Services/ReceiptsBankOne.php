<?php

declare(strict_types=1);

namespace App\Services;

use App\ApiClient\SomeMessenger\ReceiptsBotClient as SomeMessengerReceiptsBotClientAlias;
use App\ApiClient\BankOne\Client as BankOneApiClient;
use App\Entity\Receipts\BankOneTransaction;
use App\Repository\Receipts\BankOneTransactionRepository;
use DateTimeImmutable;
use DateTimeZone;
use GuzzleHttp\Exception\GuzzleException;
use RuntimeException;

class ReceiptsBankOne
{
    private string $accountId;

    public function __construct(
        private readonly BankOneApiClient $bankOneApiClient,
        private readonly SomeMessengerReceiptsBotClientAlias $receiptsBotClient,
        private readonly BankOneTransactionRepository $bankOneOperationRepository
    ) {}

    /**
     * @throws GuzzleException
     * @throws \JsonException
     */
    public function process(): void
    {
        $this->load();
        $this->publish();
    }

    /**
     * @throws GuzzleException
     * @throws \JsonException
     * @throws \Exception
     */
    public function load(): void
    {
        $transactions = $this->getLatestTransactions();
        $transactions = array_reverse($transactions);
        /** @var array $transaction */
        foreach ($transactions as $transaction) {
            if (!($transaction['creditDebitIndicator'] === 'Credit' && $transaction['transactionTypeCode'] === 'Платежное поручение')) {
                continue;
            }
            if (
                (isset($transaction['DebtorParty']['inn'])
                    and $transaction['DebtorParty']['inn'] === '7728168971'
                    || $transaction['DebtorParty']['inn'] === '4345497285'
                    || $transaction['DebtorParty']['inn'] === '7750005725'
                    || $transaction['DebtorParty']['inn'] === '4346002922')
                || mb_stripos($transaction['description'], 'процентов по депозиту') !== false
            ) {
                continue;
            }
            $entity = $this->bankOneOperationRepository->findOneByCriteriaOrNew([
                'transactionId' => $transaction['transactionId'],
                'paymentId' => $transaction['paymentId'],
            ]);
            if ($entity->getId() === null) {
                $entity = $this->bankOneOperationRepository->populateByTransactionData($entity, $transaction);
                $this->bankOneOperationRepository->add($entity);
            }
        }
        $this->bankOneOperationRepository->save();
    }

    /**
     * @throws GuzzleException
     * @throws \JsonException
     * @throws \Exception
     */
    public function publish(): void
    {
        /** @var BankOneTransaction[] $unpublished */
        $unpublished = $this->bankOneOperationRepository->findUnpublished();
        foreach ($unpublished as $entity) {
            $message = $this->createMessage($entity);
            $result = $this->receiptsBotClient->sendMessage($message);
            if (is_int($result['result']) && $result['result'] > 0) {
                $entity->setBxMessageId($result['result']);
                $entity->setPublishedAt(new DateTimeImmutable($result['time']['date_finish']));
                $this->bankOneOperationRepository->save();
                sleep(1);
            }
        }
    }

    public function createMessage(BankOneTransaction $transaction): string
    {
        $amount = $transaction->getAmount();
        $currency = $amount['currency'] ?? '';
        $amount = $amount['amount'] ?? '';
        $debtorParty = $transaction->getDebtorParty();
        $name = $debtorParty['name'] ?? '';
        $inn = $debtorParty['inn'] ?? '';
        $debtorAccount = $transaction->getDebtorAccount();
        $debtorAccountIdentification = $debtorAccount['identification'] ?? '';
        $documentNumber = $transaction->getDocumentNumber() ?? '';
        $description = $transaction->getDescription() ?? '';
        return <<<EOT
Банк 1
Входящий платёж на {$amount} {$currency}
Контрагент: {$name}
ИНН: {$inn}
Номер счёта: {$debtorAccountIdentification}
Номер платёжного документа: {$documentNumber}
{$description}
EOT;
    }

    /**
     * @throws GuzzleException
     * @throws \JsonException
     * @throws \Exception
     */
    public function getLatestTransactions(): array
    {
        $statement = $this->bankOneApiClient->initStatement(
            $this->accountId,
            new DateTimeImmutable('-3 days', new DateTimeZone('Europe/Moscow')),
            new DateTimeImmutable('now', new DateTimeZone('Europe/Moscow'))
        );
        sleep(5);
        for ($i = 0; $i < 5; $i++) {
            $statement = $this->bankOneApiClient->getStatement($this->accountId, $statement['statementId']);
            if (strtolower($statement['status']) === 'ready') {
                return $statement['Transaction'];
            }
            sleep(5);
        }
        throw new RuntimeException('Не удалось получить транзакции выписки ' . $statement['statementId']);
    }

    public function setAccountId(string $accountId): void
    {
        $this->accountId = $accountId;
    }
}