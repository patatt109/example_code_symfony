<?php

namespace App\Repository\Receipts;

use App\Entity\Receipts\BankOneTransaction;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BankOneTransaction>
 *
 * @method BankOneTransaction|null find($id, $lockMode = null, $lockVersion = null)
 * @method BankOneTransaction|null findOneBy(array $criteria, array $orderBy = null)
 * @method BankOneTransaction[]    findAll()
 * @method BankOneTransaction[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BankOneTransactionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BankOneTransaction::class);
    }

    public function add(BankOneTransaction $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(BankOneTransaction $entity, bool $flush = false): void
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

    public function findOneByCriteriaOrNew(array $criteria = []): BankOneTransaction
    {
        return $this->findOneBy($criteria) ?? new BankOneTransaction();
    }

    /**
     * @throws \Exception
     */
    public function populateByTransactionData(BankOneTransaction $entity, array $data): BankOneTransaction
    {
        return $entity
            ->setTransactionId($data['transactionId'])
            ->setPaymentId($data['paymentId'])
            ->setCreditDebitIndicator($data['creditDebitIndicator'])
            ->setStatus($data['status'])
            ->setDocumentNumber($data['documentNumber'])
            ->setTransactionTypeCode($data['transactionTypeCode'])
            ->setDocumentProcessDate(new DateTimeImmutable($data['documentProcessDate']))
            ->setDescription($data['description'] ?? null)
            ->setAmount($data['Amount'] ?? null)
            ->setDebtorParty($data['DebtorParty'] ?? null)
            ->setDebtorAccount($data['DebtorAccount'] ?? null)
            ->setDebtorAgent($data['DebtorAgent'] ?? null)
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
