<?php

namespace App\Telegram\Command;

use App\Entity\Conversation;
use App\Repository\NotificationRepository;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Types\Update;
use Twig\Environment;

class ListCommand implements CommandInterface
{
    public const NAME = '/list';

    public function __construct(
        private BotApi $bot,
        private NotificationRepository $notificationRepository,
        private Environment $twig
    )
    {

    }

    public function handle(Update $update, ?Conversation $conversation = null): void
    {
        $message = $this->twig->render('list.html.twig', [
            'notifications' => $this->notificationRepository->findByChatId($update->getMessage()->getChat()->getId())
        ]);

        $this->bot->sendMessage($update->getMessage()->getChat()->getId(), $message, 'html');
    }
}
