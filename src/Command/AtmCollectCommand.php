<?php

namespace App\Command;

use App\Entity\Atm;
use App\Entity\Cluster;
use App\Repository\AtmRepository;
use App\Repository\ClusterRepository;
use App\Tinkoff\HttpClient;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'atm:collect')]
class AtmCollectCommand extends Command
{
    public function __construct(
        private HttpClient $httpClient,
        private ClusterRepository $clusterRepository,
        private AtmRepository $atmRepository,
        private EntityManagerInterface $entityManager,
        private LoggerInterface $logger
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('latitudeMin', null, InputOption::VALUE_REQUIRED, default: 0)
            ->addOption('latitudeMax', null, InputOption::VALUE_REQUIRED, default: 180)
            ->addOption('latitudeStep', null, InputOption::VALUE_REQUIRED, default: 0.1)
            ->addOption('longitudeMin', null, InputOption::VALUE_REQUIRED, default: 0)
            ->addOption('longitudeMax', null, InputOption::VALUE_REQUIRED, default: 180)
            ->addOption('longitudeStep', null, InputOption::VALUE_REQUIRED, default: 0.1);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $latitudeMin  = (float)$input->getOption('latitudeMin');
        $latitudeMax  = (float)$input->getOption('latitudeMax');
        $latitudeStep = (float)$input->getOption('latitudeStep');

        $longitudeMin  = (float)$input->getOption('longitudeMin');
        $longitudeMax  = (float)$input->getOption('longitudeMax');
        $longitudeStep = (float)$input->getOption('longitudeStep');

        $index = 0;
        $count = (int)(($latitudeMax - $latitudeMin) / $latitudeStep * ($longitudeMax - $longitudeMin) / $longitudeStep);

        $clustersCache = [];
        $atmsCache     = [];

        for ($latitude = $latitudeMin; $latitude <= $latitudeMax; $latitude += $latitudeStep) {
            for ($longitude = $longitudeMin; $longitude <= $longitudeMax; $longitude += $longitudeStep) {
                $index++;

                $clustersDto = $this->httpClient->getClusters(
                    $latitude,
                    $longitude,
                    $latitude + $latitudeStep,
                    $longitude + $longitudeStep
                );

                foreach ($clustersDto as $clusterDto) {

                    $clusterId = $clusterDto->id;

                    if (empty($clusterId)) {
                        continue;
                    }

                    $cluster = null;
                    if (isset($clustersCache[$clusterId])) {
                        $cluster = $clustersCache[$clusterId];
                    }

                    if ($cluster === null) {
                        $cluster = $this->clusterRepository->findOneById($clusterId);
                    }

                    if ($cluster === null) {
                        $cluster = new Cluster($clusterId);

                        $cluster->setBottomLeftLatitude($clusterDto->bottomLeftCoordinates->latitude);
                        $cluster->setBottomLeftLongitude($clusterDto->bottomLeftCoordinates->longitude);
                        $cluster->setTopRightLatitude($clusterDto->topRightCoordinates->latitude);
                        $cluster->setTopRightLongitude($clusterDto->topRightCoordinates->longitude);

                        $this->entityManager->persist($cluster);
                    }

                    $clustersCache[$clusterId] = $cluster;

                    foreach ($clusterDto->points as $pointDto) {

                        $atmId = $pointDto->id;
                        if (empty($atmId)) {
                            continue;
                        }

                        $atm = null;
                        if (isset($atmsCache[$atmId])) {
                            $atm = $atmsCache[$atmId];
                        }

                        if ($atm === null) {
                            $atm = $this->atmRepository->findOneById($atmId);
                        }

                        if ($atm === null) {
                            $atm = new Atm($atmId, $cluster);
                            $atm->setAddress($pointDto->address);
                            $this->entityManager->persist($atm);
                        }

                        $atm->setUsd(0);
                        $atm->setRub(0);
                        $atm->setEur(0);

                        foreach ($pointDto->limits as $limitDto) {
                            switch ($limitDto->currency) {
                                case Atm::USD:
                                    $atm->setUsd($limitDto->amount);
                                    break;
                                case Atm::RUB:
                                    $atm->setRub($limitDto->amount);
                                    break;
                                case Atm::EUR:
                                    $atm->setEur($limitDto->amount);
                                    break;
                            }
                        }
                        $atm->setUpdatedAt(new \DateTimeImmutable());

                        $atmsCache[$atmId] = $atm;
                    }
                }

                $this->logger->info('Collect', ['index' => $index, 'count' => $count]);

                if ($index % 1000 === 0) {
                    $this->entityManager->flush();
                }

                usleep(200_000);
            }
        }

        $this->entityManager->flush();

        return Command::SUCCESS;
    }
}
