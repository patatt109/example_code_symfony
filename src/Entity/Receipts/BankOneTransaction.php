<?php

namespace App\Entity\Receipts;

use App\Repository\Receipts\BankOneTransactionRepository;
use DateTimeImmutable;
use DateTimeZone;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\UniqueConstraint;

#[ORM\Entity(repositoryClass: BankOneTransactionRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[Table]
#[UniqueConstraint(columns: ['transaction_id', 'payment_id'])]
class BankOneTransaction
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $transactionId;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $paymentId;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $creditDebitIndicator;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $status;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $documentNumber;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $transactionTypeCode;

    #[ORM\Column(type: 'date', nullable: true)]
    private $documentProcessDate;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $description;

    #[ORM\Column(type: 'json', nullable: true)]
    private $amount = [];

    #[ORM\Column(type: 'json', nullable: true)]
    private $debtorParty = [];

    #[ORM\Column(type: 'json', nullable: true)]
    private $debtorAccount = [];

    #[ORM\Column(type: 'json', nullable: true)]
    private $debtorAgent = [];

    #[ORM\Column(type: 'bigint', nullable: true, unique: true)]
    private $bxMessageId;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private $publishedAt;

    #[ORM\Column(type: 'datetime_immutable', columnDefinition: 'DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP')]
    private $createdAt;

    #[ORM\Column(type: 'datetime_immutable', nullable: true, columnDefinition: 'DATETIME DEFAULT NULL on update CURRENT_TIMESTAMP')]
    private $updatedAt;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTransactionId(): ?string
    {
        return $this->transactionId;
    }

    public function setTransactionId(?string $transactionId): self
    {
        $this->transactionId = $transactionId;

        return $this;
    }

    public function getPaymentId(): ?string
    {
        return $this->paymentId;
    }

    public function setPaymentId(?string $paymentId): self
    {
        $this->paymentId = $paymentId;

        return $this;
    }

    public function getCreditDebitIndicator(): ?string
    {
        return $this->creditDebitIndicator;
    }

    public function setCreditDebitIndicator(?string $creditDebitIndicator): self
    {
        $this->creditDebitIndicator = $creditDebitIndicator;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getDocumentNumber(): ?string
    {
        return $this->documentNumber;
    }

    public function setDocumentNumber(?string $documentNumber): self
    {
        $this->documentNumber = $documentNumber;

        return $this;
    }

    public function getTransactionTypeCode(): ?string
    {
        return $this->transactionTypeCode;
    }

    public function setTransactionTypeCode(?string $transactionTypeCode): self
    {
        $this->transactionTypeCode = $transactionTypeCode;

        return $this;
    }

    public function getDocumentProcessDate(): ?\DateTimeInterface
    {
        return $this->documentProcessDate;
    }

    public function setDocumentProcessDate(?\DateTimeInterface $documentProcessDate): self
    {
        $this->documentProcessDate = $documentProcessDate;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getAmount(): ?array
    {
        return $this->amount;
    }

    public function setAmount(?array $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function getDebtorParty(): ?array
    {
        return $this->debtorParty;
    }

    public function setDebtorParty(?array $debtorParty): self
    {
        $this->debtorParty = $debtorParty;

        return $this;
    }

    public function getDebtorAccount(): ?array
    {
        return $this->debtorAccount;
    }

    public function setDebtorAccount(?array $debtorAccount): self
    {
        $this->debtorAccount = $debtorAccount;

        return $this;
    }

    public function getDebtorAgent(): ?array
    {
        return $this->debtorAgent;
    }

    public function setDebtorAgent(?array $debtorAgent): self
    {
        $this->debtorAgent = $debtorAgent;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getBxMessageId()
    {
        return $this->bxMessageId;
    }

    /**
     * @param mixed $bxMessageId
     * @return BankOneTransaction
     */
    public function setBxMessageId($bxMessageId)
    {
        $this->bxMessageId = $bxMessageId;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPublishedAt()
    {
        return $this->publishedAt;
    }

    /**
     * @param mixed $publishedAt
     * @return BankOneTransaction
     */
    public function setPublishedAt($publishedAt)
    {
        $this->publishedAt = $publishedAt;
        return $this;
    }

    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @throws \Exception
     */
    #[ORM\PrePersist]
    public function doStuffOnPrePersist(PrePersistEventArgs $eventArgs): void
    {
        $this->createdAt = new DateTimeImmutable('now', new DateTimeZone('Europe/Moscow'));
    }

    public function getUpdatedAt(): ?DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?DateTimeImmutable $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @throws \Exception
     */
    #[ORM\PreUpdate]
    public function doStuffOnPreUpdate(PreUpdateEventArgs $eventArgs): void
    {
        $this->updatedAt = new DateTimeImmutable('now', new DateTimeZone('Europe/Moscow'));
    }
}
