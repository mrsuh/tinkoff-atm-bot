<?php

namespace App\Repository;

use App\Entity\Notification;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Notification|null find($id, $lockMode = null, $lockVersion = null)
 * @method Notification|null findOneBy(array $criteria, array $orderBy = null)
 * @method Notification[]    findAll()
 * @method Notification[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NotificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Notification::class);
    }

    /**
     * @return Notification[]
     */
    public function findByChatId(string $chatId): array
    {
        return $this->findBy(['chatId' => $chatId]);
    }

    public function countAll(): int
    {
        $result = $this
            ->createQueryBuilder('n')
            ->select('COUNT(n.id) as count')
            ->getQuery()
            ->getOneOrNullResult();

        return $result['count'] ?? 0;
    }

    public function countHandled(): int
    {
        $result = $this
            ->createQueryBuilder('n')
            ->select('COUNT(n.id) as count')
            ->where('n.handled = :handled')
            ->setParameter('handled', true)
            ->getQuery()
            ->getOneOrNullResult();

        return $result['count'] ?? 0;
    }

    public function countUsers(): int
    {
        $result = $this
            ->createQueryBuilder('n')
            ->select('COUNT(n.chatId) as count')
            ->groupBy('n.chatId')
            ->getQuery()
            ->getOneOrNullResult();

        return $result['count'] ?? 0;
    }
}
