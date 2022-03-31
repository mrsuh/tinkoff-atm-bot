<?php

namespace App\Command;

use App\Entity\Atm;
use App\Entity\Notification;

class AtmNotification
{
    private Atm $atm;
    /** @var Notification[] */
    private array $notifications;

    public function __construct(Atm $atm)
    {
        $this->atm = $atm;
    }

    public function getAtm(): Atm
    {
        return $this->atm;
    }

    /**
     * @return Notification[]
     */
    public function getNotifications(): array
    {
        return $this->notifications;
    }

    public function addNotification(Notification $notification): void
    {
        $this->notifications[] = $notification;
    }

}
