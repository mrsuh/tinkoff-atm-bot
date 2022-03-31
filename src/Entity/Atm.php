<?php

namespace App\Entity;

use App\Repository\AtmRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AtmRepository::class)]
class Atm
{
    public const USD = 'USD';
    public const RUB = 'RUB';
    public const EUR = 'EUR';

    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 6)]
    private string $id;

    #[ORM\ManyToOne(targetEntity: Cluster::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Cluster $cluster;

    #[ORM\Column(type: 'string', length: 255)]
    private string $address = '';

    #[ORM\Column(type: 'integer')]
    private int $usd = 0;

    #[ORM\Column(type: 'integer')]
    private int $rub = 0;

    #[ORM\Column(type: 'integer')]
    private int $eur = 0;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $updatedAt;

    public function __construct(string $id, Cluster $cluster)
    {
        $this->id        = $id;
        $this->cluster   = $cluster;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getCluster(): Cluster
    {
        return $this->cluster;
    }

    public function getAddress(): string
    {
        return $this->address;
    }

    public function setAddress(string $address): void
    {
        $this->address = $address;
    }

    public function getUsd(): int
    {
        return $this->usd;
    }

    public function setUsd(int $usd): void
    {
        $this->usd = $usd;
    }

    public function getRub(): int
    {
        return $this->rub;
    }

    public function setRub(int $rub): void
    {
        $this->rub = $rub;
    }

    public function getEur(): int
    {
        return $this->eur;
    }

    public function setEur(int $eur): void
    {
        $this->eur = $eur;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }
}
