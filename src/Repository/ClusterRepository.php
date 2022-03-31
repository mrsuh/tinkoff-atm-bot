<?php

namespace App\Repository;

use App\Entity\Cluster;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Cluster|null find($id, $lockMode = null, $lockVersion = null)
 * @method Cluster|null findOneBy(array $criteria, array $orderBy = null)
 * @method Cluster[]    findAll()
 * @method Cluster[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ClusterRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Cluster::class);
    }

    public function findOneById(string $id): ?Cluster
    {
        return $this->findOneBy(['id' => $id]);
    }
}
