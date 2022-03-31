<?php

namespace App\Telegram\Command;

use App\Entity\Conversation;
use TelegramBot\Api\Types\Update;

interface CommandInterface
{
    public function handle(Update $update, ?Conversation $conversation = null): void;
}
