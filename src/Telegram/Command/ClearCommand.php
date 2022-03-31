<?php

namespace App\Telegram\Command;

use App\Entity\Conversation;
use App\Repository\ConversationRepository;
use App\Repository\NotificationRepository;
use Doctrine\ORM\EntityManagerInterface;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Types\ReplyKeyboardMarkup;
use TelegramBot\Api\Types\ReplyKeyboardRemove;
use TelegramBot\Api\Types\Update;

class ClearCommand implements CommandInterface
{
    public const NAME = '/clear';

    public const STATE_CONFIRMATION = 1;

    public function __construct(
        private BotApi $bot,
        private NotificationRepository $notificationRepository,
        private ConversationRepository $conversationRepository,
        private EntityManagerInterface $entityManager
    )
    {

    }

    public function handle(Update $update, ?Conversation $conversation = null): void
    {
        $text   = $update->getMessage()->getText();
        $chatId = $update->getMessage()->getChat()->getId();

        if ($conversation === null) {
            $conversation = new Conversation($chatId, self::NAME, self::STATE_CONFIRMATION);
            $this->entityManager->persist($conversation);
            $this->entityManager->flush();
            $keyboard = new ReplyKeyboardMarkup([['Да', 'Нет']], true, true);
            $this->bot->sendMessage($chatId, 'Вы уверены, что хотите очистить все напоминания?', replyMarkup: $keyboard);

            return;
        }

        switch ($conversation->getState()) {
            case self::STATE_CONFIRMATION:
                $this->entityManager->beginTransaction();
                try {
                    if ($text === 'Да') {
                        foreach ($this->notificationRepository->findByChatId($chatId) as $notification) {
                            $this->entityManager->remove($notification);
                        }
                    }

                    $this->conversationRepository->deleteByChatId($chatId);
                    $this->entityManager->flush();
                    $this->entityManager->commit();
                } catch (\Exception $exception) {
                    $this->entityManager->rollback();

                    throw $exception;
                }

                $this->bot->sendMessage($chatId, 'Готово!', replyMarkup: new ReplyKeyboardRemove());

                break;
            default:
                throw new \RuntimeException();
        }
    }
}
