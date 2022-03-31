<?php

namespace App\Command;

use App\Entity\Atm;
use App\Repository\NotificationRepository;
use App\Telegram\Bot;
use App\Tinkoff\DTO\Limit;
use App\Tinkoff\HttpClient;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'atm:notify')]
class AtmNotifyCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private NotificationRepository $notificationRepository,
        private HttpClient $httpClient,
        private Bot $bot
    )
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $clusters         = [];
        $atmNotifications = [];
        foreach ($this->notificationRepository->findAll() as $notification) {
            $clusters[$notification->getAtm()->getCluster()->getId()] = $notification->getAtm()->getCluster();

            $atm   = $notification->getAtm();
            $atmId = $atm->getId();
            if (!isset($atmNotifications[$atmId])) {
                $atmNotifications[$atmId] = new AtmNotification($atm);
            }
            $atmNotifications[$atmId]->addNotification($notification);
        }

        $pointsDto = [];
        foreach ($clusters as $cluster) {
            $clustersDto = $this->httpClient->getClusters(
                $cluster->getBottomLeftLatitude(),
                $cluster->getBottomLeftLongitude(),
                $cluster->getTopRightLatitude(),
                $cluster->getTopRightLongitude()
            );
            foreach ($clustersDto as $clusterDto) {
                foreach ($clusterDto->points as $pointDto) {
                    $pointsDto[$pointDto->id] = $pointDto;
                }
            }
        }

        foreach ($atmNotifications as $atmNotification) {
            $atm   = $atmNotification->getAtm();
            $atmId = $atm->getId();

            if (!isset($pointsDto[$atmId])) {
                continue;
            }

            $atm->setUsd(0);
            $atm->setRub(0);
            $atm->setEur(0);
            $pointDto = $pointsDto[$atmId];
            foreach ($pointDto->limits as $limitDto) {
                switch ($limitDto->currency) {
                    case Limit::USD:
                        $atm->setUsd($limitDto->amount);
                        break;
                    case Limit::RUB:
                        $atm->setRub($limitDto->amount);
                        break;
                    case Limit::EUR:
                        $atm->setEur($limitDto->amount);
                        break;
                }
            }

            foreach ($atmNotification->getNotifications() as $notification) {

                $atmAmount = 0;
                switch ($notification->getCurrency()) {
                    case Atm::USD:
                        $atmAmount = $atm->getUsd();
                        break;
                    case Atm::RUB:
                        $atmAmount = $atm->getRub();
                        break;
                    case Atm::EUR:
                        $atmAmount = $atm->getEur();
                        break;
                }

                if ($atmAmount >= $notification->getAmount()) {
                    if (!$notification->isHandled()) {
                        $this->bot->notify($notification, $atm);
                        $notification->setHandled(true);
                    }
                } else {
                    $notification->setHandled(false);
                }
            }

            $atm->setUpdatedAt(new \DateTimeImmutable());
        }

        $this->entityManager->flush();

        return Command::SUCCESS;
    }
}
