<?php

namespace App\Telegram\Command;

use App\Entity\Conversation;
use App\Repository\ConversationRepository;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Types\ReplyKeyboardRemove;
use TelegramBot\Api\Types\Update;

class CancelCommand implements CommandInterface
{
    public const NAME = '/cancel';

    public function __construct(
        private BotApi $bot,
        private ConversationRepository $conversationRepository
    )
    {

    }

    public function handle(Update $update, ?Conversation $conversation = null): void
    {
        $chatId = $update->getMessage()->getChat()->getId();

        $this->conversationRepository->deleteByChatId($chatId);

        $this->bot->sendMessage($chatId, 'Готово!', replyMarkup: new ReplyKeyboardRemove());
    }
}
