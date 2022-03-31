<?php

namespace App\Repository;

use App\Entity\Conversation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Conversation|null find($id, $lockMode = null, $lockVersion = null)
 * @method Conversation|null findOneBy(array $criteria, array $orderBy = null)
 * @method Conversation[]    findAll()
 * @method Conversation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ConversationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Conversation::class);
    }

    public function findOneByChatId(string $chatId): ?Conversation
    {
        return $this->findOneBy(['chatId' => $chatId]);
    }

    public function deleteByChatId(string $chatId): void
    {
        $this
            ->createQueryBuilder('c')
            ->delete()
            ->where('c.chatId = :chatId')
            ->setParameter('chatId', $chatId)
            ->getQuery()
            ->getResult();
    }
}
