<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Message\Event\TicketCreated;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\Event\WorkerMessageFailedEvent;
use Symfony\Component\Messenger\Stamp\ErrorDetailsStamp;

class TicketMessageRetriedSubscriber implements EventSubscriberInterface
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            WorkerMessageFailedEvent::class => 'onWorkerMessageFailed',
        ];
    }

    public function onWorkerMessageFailed(WorkerMessageFailedEvent $event): void
    {
        $message = $event->getEnvelope()->getMessage();
        if (!$message instanceof TicketCreated) {
            return;
        }

        $stamp = $event->getEnvelope()->last(ErrorDetailsStamp::class);

        $this->logger->error(sprintf('%s:', $stamp->getExceptionClass()), [$stamp->getExceptionMessage()]);
    }
}
