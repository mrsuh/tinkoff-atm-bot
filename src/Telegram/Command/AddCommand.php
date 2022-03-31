<?php

namespace App\Telegram\Command;

use App\Entity\Atm;
use App\Entity\Conversation;
use App\Entity\Notification;
use App\Repository\AtmRepository;
use App\Repository\ConversationRepository;
use Doctrine\ORM\EntityManagerInterface;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Types\ReplyKeyboardMarkup;
use TelegramBot\Api\Types\Update;
use Twig\Environment;

class AddCommand implements CommandInterface
{
    public const NAME = '/add';

    public const STATE_ATM      = 1;
    public const STATE_CURRENCY = 2;
    public const STATE_AMOUNT   = 3;

    public function __construct(
        private BotApi $bot,
        private AtmRepository $atmRepository,
        private ConversationRepository $conversationRepository,
        private EntityManagerInterface $entityManager,
        private Environment $twig
    )
    {

    }

    public function handle(Update $update, ?Conversation $conversation = null): void
    {
        $text   = $update->getMessage()->getText();
        $chatId = $update->getMessage()->getChat()->getId();

        if ($conversation === null) {
            $conversation = new Conversation($chatId, self::NAME, self::STATE_ATM);
            $this->entityManager->persist($conversation);
            $this->entityManager->flush();
            $this->bot->sendMessage($chatId, $this->twig->render('add.html.twig'), 'html', true);

            return;
        }

        switch ($conversation->getState()) {
            case self::STATE_ATM:
                $atm = $this->atmRepository->findOneById($text);
                if ($atm === null) {
                    $this->bot->sendMessage($chatId, 'Я не знаю такой банкомат(');

                    return;
                }
                $conversation->setData(['atm' => $atm->getId()]);
                $conversation->setState(self::STATE_CURRENCY);
                $this->entityManager->flush();
                $keyboard = new ReplyKeyboardMarkup([[Atm::USD, Atm::EUR, Atm::RUB]], true, true);

                $this->bot->sendMessage($chatId, 'Какая валюта?', replyMarkup: $keyboard);
                break;
            case self::STATE_CURRENCY:
                if (!in_array($text, [Atm::USD, Atm::EUR, Atm::RUB])) {
                    $this->bot->sendMessage($chatId, 'Я не знаю такую валюту(');

                    return;
                }
                $conversation->updateData(['currency' => $text]);
                $conversation->setState(self::STATE_AMOUNT);
                $this->entityManager->flush();
                $this->bot->sendMessage($chatId, 'При достижении какого количества уведомлять?');
                break;
            case self::STATE_AMOUNT:
                if ((int)$text < 0) {
                    $this->bot->sendMessage($chatId, 'Я не понимаю(');

                    return;
                }

                $data = $conversation->getData();
                $atm  = $this->atmRepository->findOneById($data['atm']);
                if ($atm === null) {
                    throw new \RuntimeException();
                }

                $currency = $data['currency'];
                if (!in_array($currency, [Atm::USD, Atm::EUR, Atm::RUB])) {
                    throw new \RuntimeException();
                }

                $notification = new Notification($atm);
                $notification->setChatId($chatId);
                $notification->setAmount((int)$text);
                $notification->setCurrency($data['currency']);

                $this->entityManager->persist($notification);
                $this->entityManager->beginTransaction();
                try {
                    $this->conversationRepository->deleteByChatId($chatId);
                    $this->entityManager->flush();
                    $this->entityManager->commit();
                } catch (\Exception $exception) {
                    $this->entityManager->rollback();

                    throw  $exception;
                }

                $this->bot->sendMessage($update->getMessage()->getChat()->getId(), 'Отлично, я все запомнил!');
                break;
            default:
                throw new \RuntimeException();
        }
    }
}
