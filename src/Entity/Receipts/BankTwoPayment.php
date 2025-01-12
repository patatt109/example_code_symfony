<?php

namespace App\Entity\Receipts;

use App\Repository\Receipts\BankTwoPaymentRepository;
use DateTimeImmutable;
use DateTimeZone;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BankTwoPaymentRepository::class)]
#[ORM\HasLifecycleCallbacks]
class BankTwoPayment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255, unique: true)]
    private $paymentId;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $status;

    #[ORM\Column(type: 'integer')]
    private $amount;

    #[ORM\Column(type: 'string', length: 255)]
    private $currency;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $description;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private $paymentCreatedAt;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private $paymentCapturedAt;

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

    public function getPaymentId(): ?string
    {
        return $this->paymentId;
    }

    public function setPaymentId(?string $paymentId): self
    {
        $this->paymentId = $paymentId;

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

    public function getAmount(): ?int
    {
        return $this->amount;
    }

    public function setAmount(int $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    public function setCurrency(string $currency): self
    {
        $this->currency = $currency;

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

    public function getPaymentCreatedAt(): ?DateTimeImmutable
    {
        return $this->paymentCreatedAt;
    }

    public function setPaymentCreatedAt(?DateTimeImmutable $paymentCreatedAt): self
    {
        $this->paymentCreatedAt = $paymentCreatedAt;

        return $this;
    }

    public function getPaymentCapturedAt(): ?DateTimeImmutable
    {
        return $this->paymentCapturedAt;
    }

    public function setPaymentCapturedAt(?DateTimeImmutable $paymentCapturedAt): self
    {
        $this->paymentCapturedAt = $paymentCapturedAt;

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
     * @return BankTwoPayment
     */
    public function setBxMessageId($bxMessageId)
    {
        $this->bxMessageId = $bxMessageId;
        return $this;
    }

    public function getPublishedAt(): ?DateTimeImmutable
    {
        return $this->publishedAt;
    }

    public function setPublishedAt(?DateTimeImmutable $publishedAt): self
    {
        $this->publishedAt = $publishedAt;

        return $this;
    }

    public function getCreatedAt(): DateTimeImmutable
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
