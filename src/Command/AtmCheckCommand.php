<?php

namespace App\Command;

use App\Entity\Atm;
use App\Repository\AtmRepository;
use App\Tinkoff\HttpClient;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'atm:check')]
class AtmCheckCommand extends Command
{
    public function __construct(
        private AtmRepository $atmRepository,
        private HttpClient $httpClient,
        private EntityManagerInterface $entityManager
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('id', null, InputOption::VALUE_REQUIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $id  = $input->getOption('id');
        $atm = $this->atmRepository->findOneById($id);
        if ($atm === null) {
            return Command::FAILURE;
        }

        $cluster     = $atm->getCluster();
        $clustersDto = $this->httpClient->getClusters(
            $cluster->getBottomLeftLatitude(),
            $cluster->getBottomLeftLongitude(),
            $cluster->getTopRightLatitude(),
            $cluster->getTopRightLongitude()
        );

        $found = false;
        foreach ($clustersDto as $clusterDto) {
            if ($clusterDto->id !== $cluster->getId()) {
                continue;
            }

            $found = true;

            foreach ($clusterDto->points as $pointDto) {
                if ($pointDto->id !== $atm->getId()) {
                    continue;
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
            }
        }

        if (!$found) {
            return Command::FAILURE;
        }

        $this->entityManager->flush();

        $table = new Table($output);
        $table->setHeaders(['id', 'address', 'updatedAt', 'limits']);
        $limits = [
            sprintf('%s: %s', Atm::USD, number_format($atm->getUsd())),
            sprintf('%s: %s', Atm::RUB, number_format($atm->getRub())),
            sprintf('%s: %s', Atm::EUR, number_format($atm->getEur())),
        ];
        $table->addRow([
            $atm->getId(),
            $atm->getAddress(),
            $atm->getUpdatedAt()->format(\DateTimeInterface::RFC3339),
            implode(PHP_EOL, $limits)
        ]);

        $table->render();

        return Command::SUCCESS;
    }
}
