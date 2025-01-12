<?php

namespace App\Repository\Receipts;

use App\Entity\Receipts\BankTwoPayment;
use DateTimeImmutable;
use DateTimeZone;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use BankTwo\Model\Payment\PaymentInterface;

/**
 * @extends ServiceEntityRepository<BankTwoPayment>
 *
 * @method BankTwoPayment|null find($id, $lockMode = null, $lockVersion = null)
 * @method BankTwoPayment|null findOneBy(array $criteria, array $orderBy = null)
 * @method BankTwoPayment[]    findAll()
 * @method BankTwoPayment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BankTwoPaymentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BankTwoPayment::class);
    }

    public function add(BankTwoPayment $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(BankTwoPayment $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function save(bool $clear = false): void
    {
        $this->getEntityManager()->flush();

        if ($clear) {
            $this->getEntityManager()->clear();
        }
    }

    public function findOneByCriteriaOrNew(array $criteria = []): BankTwoPayment
    {
        return $this->findOneBy($criteria) ?? new BankTwoPayment();
    }

    /**
     * @throws \Exception
     */
    public function populateByApiModel(BankTwoPayment $entity, PaymentInterface $model): BankTwoPayment
    {
        $metadata = $model->getMetadata()?->toArray();
        $description = '';
        if (!empty($metadata['customerNumber'])) {
            $description .= "Контрагент: {$metadata['customerNumber']}" . PHP_EOL;
        }
        if (!empty($metadata['lead_id'])) {
            $description .= "Номер сделки в СРМ: {$metadata['lead_id']}" . PHP_EOL;
        }
        return $entity
            ->setPaymentId($model->getId())
            ->setStatus($model->getStatus())
            ->setAmount($model->getAmount()->getIntegerValue())
            ->setCurrency($model->getAmount()->getCurrency())
            ->setDescription($description . $model->getDescription())
            ->setPaymentCreatedAt(
                (new DateTimeImmutable($model->getCreatedAt()->format('Y-m-d H:i:s'), new DateTimeZone('UTC')))
                    ->setTimezone(new DateTimeZone('Europe/Moscow'))
            )
            ->setPaymentCapturedAt(
                (new DateTimeImmutable($model->getCapturedAt()->format('Y-m-d H:i:s'), new DateTimeZone('UTC')))
                    ->setTimezone(new DateTimeZone('Europe/Moscow'))
            )
            ;
    }

    public function findUnpublished(): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.bxMessageId IS NULL')
            ->orderBy('t.id', 'ASC')
            ->getQuery()
            ->getResult()
            ;
    }
}
