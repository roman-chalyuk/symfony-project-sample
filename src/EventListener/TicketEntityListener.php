<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Entity\Ticket;
use App\Message\Event\TicketCreated;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class TicketEntityListener
{
    private LoggerInterface $logger;
    private MessageBusInterface $eventBus;
    private static array $storage = [];

    public function __construct(
        LoggerInterface $logger,
        MessageBusInterface $eventBus
    ) {
        $this->logger = $logger;
        $this->eventBus = $eventBus;
    }

    public function postPersist(Ticket $ticket, LifecycleEventArgs $event): void
    {
        if ('registered' === $ticket->getState()) {
            self::$storage[] = $ticket->getId();
        }
    }

    public function postFlush(PostFlushEventArgs $event): void
    {
        if (!self::$storage) {
            return;
        }

        foreach (self::$storage as $ticketId) {
            try {
                $this->eventBus->dispatch(new TicketCreated($ticketId));
            } catch (\Throwable $e) {
                $this->logger->critical(
                    'Ticket dispatch error.',
                    [
                        'id' => $ticketId,
                        'error' => $e->getMessage()
                    ]
                );
            }
        }

        self::$storage = [];
    }
}
