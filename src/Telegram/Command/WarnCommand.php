<?php

namespace App\Telegram\Command;

use App\Entity\Conversation;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Types\Update;
use Twig\Environment;

class WarnCommand implements CommandInterface
{
    public const NAME = '/warn';

    public function __construct(private BotApi $bot, private Environment $twig)
    {

    }

    public function handle(Update $update, ?Conversation $conversation = null): void
    {
        $this->bot->sendMessage(
            $update->getMessage()->getChat()->getId(),
            $this->twig->render('warn.html.twig'),
            'html',
            true
        );
    }
}
