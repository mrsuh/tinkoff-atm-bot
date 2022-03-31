<?php

namespace App\Entity;

use App\Repository\NotificationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: NotificationRepository::class)]
class Notification
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id;

    #[ORM\Column(type: 'string', length: 255)]
    private string $chatId;

    #[ORM\Column(type: 'string', length: 3)]
    private string $currency = Atm::USD;

    #[ORM\Column(type: 'integer')]
    private int $amount = 0;

    #[ORM\ManyToOne(targetEntity: Atm::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Atm $atm;

    #[ORM\Column(type: 'boolean')]
    private bool $handled = false;

    public function __construct(Atm $atm)
    {
        $this->atm = $atm;
    }

    public function getId(): int
    {
        return (int)$this->id;
    }

    public function getChatId(): string
    {
        return $this->chatId;
    }

    public function setChatId(string $chatId): self
    {
        $this->chatId = $chatId;

        return $this;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function setCurrency(string $currency): void
    {
        $this->currency = $currency;
    }

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function setAmount(int $amount): void
    {
        $this->amount = $amount;
    }

    public function getAtm(): Atm
    {
        return $this->atm;
    }

    public function isHandled(): bool
    {
        return $this->handled;
    }

    public function setHandled(bool $handled): void
    {
        $this->handled = $handled;
    }
}
