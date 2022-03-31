<?php

namespace App\Telegram\Command;

use App\Entity\Conversation;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Types\Update;
use Twig\Environment;

class StartCommand implements CommandInterface
{
    public const NAME = '/start';

    public function __construct(private BotApi $bot, private Environment $twig)
    {

    }

    public function handle(Update $update, ?Conversation $conversation = null): void
    {
        $this->bot->sendMessage(
            $update->getMessage()->getChat()->getId(),
            $this->twig->render('start.html.twig'),
            'html',
            true
        );
    }
}
