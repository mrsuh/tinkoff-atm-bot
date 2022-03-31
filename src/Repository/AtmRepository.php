<?php

namespace App\Repository;

use App\Entity\Atm;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Atm|null find($id, $lockMode = null, $lockVersion = null)
 * @method Atm|null findOneBy(array $criteria, array $orderBy = null)
 * @method Atm[]    findAll()
 * @method Atm[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AtmRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Atm::class);
    }

    public function findOneById(string $id): ?Atm
    {
        return $this->findOneBy(['id' => $id]);
    }

    public function findOneLastUpdated(): ?Atm
    {
        return $this
            ->createQueryBuilder('atm')
            ->orderBy('atm.updatedAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
