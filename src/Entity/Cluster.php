<?php

namespace App\Entity;

use App\Repository\ClusterRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ClusterRepository::class)]
class Cluster
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 32)]
    private string $id;

    #[ORM\Column(type: 'float')]
    private float $bottomLeftLatitude = 0.0;

    #[ORM\Column(type: 'float')]
    private float $bottomLeftLongitude = 0.0;

    #[ORM\Column(type: 'float')]
    private float $topRightLatitude = 0.0;

    #[ORM\Column(type: 'float')]
    private float $topRightLongitude = 0.0;

    public function __construct(string $id)
    {
        $this->id = $id;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getBottomLeftLatitude(): float
    {
        return $this->bottomLeftLatitude;
    }

    public function setBottomLeftLatitude(float $bottomLeftLatitude): void
    {
        $this->bottomLeftLatitude = $bottomLeftLatitude;
    }

    public function getBottomLeftLongitude(): float
    {
        return $this->bottomLeftLongitude;
    }

    public function setBottomLeftLongitude(float $bottomLeftLongitude): void
    {
        $this->bottomLeftLongitude = $bottomLeftLongitude;
    }

    public function getTopRightLatitude(): float
    {
        return $this->topRightLatitude;
    }

    public function setTopRightLatitude(float $topRightLatitude): void
    {
        $this->topRightLatitude = $topRightLatitude;
    }

    public function getTopRightLongitude(): float
    {
        return $this->topRightLongitude;
    }

    public function setTopRightLongitude(float $topRightLongitude): void
    {
        $this->topRightLongitude = $topRightLongitude;
    }
}
