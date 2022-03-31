<?php

namespace App\Telegram;

use App\Entity\Atm;
use App\Entity\Notification;
use App\Repository\ConversationRepository;
use App\Telegram\Command\AddCommand;
use App\Telegram\Command\CancelCommand;
use App\Telegram\Command\ClearCommand;
use App\Telegram\Command\CommandInterface;
use App\Telegram\Command\ContactsCommand;
use App\Telegram\Command\HelpCommand;
use App\Telegram\Command\ListCommand;
use App\Telegram\Command\StartCommand;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Types\Update;
use Twig\Environment;

class Bot
{
    public function __construct(
        private BotApi $botApi,
        private AddCommand $addCommand,
        private ClearCommand $clearCommand,
        private ContactsCommand $contactsCommand,
        private HelpCommand $helpCommand,
        private ListCommand $listCommand,
        private StartCommand $startCommand,
        private CancelCommand $cancelCommand,
        private ConversationRepository $conversationRepository,
        private Environment $twig
    )
    {

    }

    public function getCommandByName(string $name): ?CommandInterface
    {
        $commands = [
            AddCommand::NAME      => $this->addCommand,
            ClearCommand::NAME    => $this->clearCommand,
            ContactsCommand::NAME => $this->contactsCommand,
            HelpCommand::NAME     => $this->helpCommand,
            ListCommand::NAME     => $this->listCommand,
            StartCommand::NAME    => $this->startCommand,
            CancelCommand::NAME   => $this->cancelCommand
        ];

        return $commands[$name] ?? null;
    }

    public function handle(Update $update): void
    {
        $text    = $update->getMessage()->getText();
        $chatId  = $update->getMessage()->getChat()->getId();
        $command = $this->getCommandByName($text);
        if ($command !== null) {
            $this->conversationRepository->deleteByChatId($chatId);
            $command->handle($update);

            return;
        }

        $conversation = $this->conversationRepository->findOneByChatId($chatId);
        if ($conversation === null) {
            $this->error($chatId);

            return;
        }

        $command = $this->getCommandByName($conversation->getCommandName());
        if ($command !== null) {
            $command->handle($update, $conversation);

            return;
        }

        $this->error($chatId);
    }

    public function notify(Notification $notification, Atm $atm): void
    {
        $this->botApi->sendMessage(
            $notification->getChatId(),
            $this->twig->render('notification.html.twig', ['notification' => $notification, 'atm' => $atm]),
            'html'
        );
    }

    public function error(string $chatId): void
    {
        $this->botApi->sendMessage(
            $chatId,
            $this->twig->render('error.html.twig'),
            'html'
        );
    }
}
