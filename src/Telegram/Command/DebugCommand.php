<?php

namespace App\Telegram\Command;

use App\Entity\Conversation;
use App\Repository\AtmRepository;
use App\Repository\NotificationRepository;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Types\Update;
use Twig\Environment;

class DebugCommand implements CommandInterface
{
    public const NAME = '/debug';

    public function __construct(
        private BotApi $bot,
        private NotificationRepository $notificationRepository,
        private AtmRepository $atmRepository,
        private Environment $twig
    )
    {

    }

    public function handle(Update $update, ?Conversation $conversation = null): void
    {
        $atm = $this->atmRepository->findOneLastUpdated();

        $this->bot->sendMessage(
            $update->getMessage()->getChat()->getId(),
            $this->twig->render('contacts.html.twig', [
                'notificationsCount'        => $this->notificationRepository->countAll(),
                'handledNotificationsCount' => $this->notificationRepository->countHandled(),
                'usersCount'                => $this->notificationRepository->countUsers(),
                'lastUpdatedAt'             => $atm ? $atm->getUpdatedAt()->format('Y-m-d H:i:s') : ''
            ]),
            'html',
            true
        );
    }
}
