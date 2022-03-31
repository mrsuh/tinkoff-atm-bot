<?php

namespace App\Entity;

use App\Repository\ConversationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ConversationRepository::class)]
class Conversation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id;

    #[ORM\Column(type: 'string', length: 255, unique: true)]
    private string $chatId;

    #[ORM\Column(type: 'string', length: 255)]
    private string $commandName;

    #[ORM\Column(type: 'integer')]
    private int $state;

    #[ORM\Column(type: 'json')]
    private array $data = [];

    public function __construct(string $chatId, string $commandName, int $state)
    {
        $this->chatId      = $chatId;
        $this->commandName = $commandName;
        $this->state       = $state;
    }

    public function getChatId(): ?string
    {
        return $this->chatId;
    }

    public function getCommandName(): string
    {
        return $this->commandName;
    }

    public function getState(): ?int
    {
        return $this->state;
    }

    public function setState(int $state): self
    {
        $this->state = $state;

        return $this;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function setData(array $data): self
    {
        $this->data = $data;

        return $this;
    }

    public function updateData(array $data): self
    {
        $this->data += $data;

        return $this;
    }
}
