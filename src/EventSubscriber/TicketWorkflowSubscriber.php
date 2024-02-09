<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\DBAL\TicketState;
use App\Entity\Ticket;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\GuardEvent;
use Symfony\Component\Workflow\Event\TransitionEvent;

class TicketWorkflowSubscriber implements EventSubscriberInterface
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'workflow.ticket.guard.pay' => ['onPayGuard'],
            'workflow.ticket.transition.pay' => ['onPayTransition'],
            'workflow.ticket.guard.cancel' => ['onCancelGuard'],
            'workflow.ticket.transition.cancel' => ['onCancelTransition'],
        ];
    }

    public function onPayGuard(GuardEvent $event): void
    {
        if (PHP_SAPI !== 'cli') {
            $event->setBlocked(true, 'Access denied');
        }
    }

    public function onPayTransition(TransitionEvent $event): void
    {
        /** @var Ticket $ticket */
        $ticket = $event->getSubject();

        $ticket
            ->setState(TicketState::PAID)
            ->setProcessedAt(new \DateTime());
    }

    public function onCancelGuard(GuardEvent $event): void
    {
        if (PHP_SAPI !== 'cli') {
            $event->setBlocked(true, 'Access denied');
        }
    }

    public function onCancelTransition(TransitionEvent $event): void
    {
        /** @var Ticket $ticket */
        $ticket = $event->getSubject();
        $context = $event->getContext();

        $ticket->setState(TicketState::CANCELED);
        if (!empty($context['message'])) {
            $this->logger->error($context['message'], ['ticket_id' => $ticket->getId()]);
        }
    }
}
