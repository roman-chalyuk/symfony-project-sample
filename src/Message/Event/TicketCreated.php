<?php

declare(strict_types=1);

namespace App\Message\Event;

class TicketCreated
{
    private int $ticketId;

    public function __construct(int $ticketId)
    {
        $this->ticketId = $ticketId;
    }

    public function getTicketId(): int
    {
        return $this->ticketId;
    }
}
