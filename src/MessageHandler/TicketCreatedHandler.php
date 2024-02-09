<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\DBAL\TicketState;
use App\Message\Event\TicketCreated;
use App\Repository\TicketRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Workflow\Registry;

#[AsMessageHandler]
class TicketCreatedHandler
{
    private LoggerInterface $logger;
    private EntityManagerInterface $entityManager;
    private TicketRepository $ticketRepository;
    private Registry $workflowRegistry;

    public function __construct(
        LoggerInterface $incomeLogger,
        EntityManagerInterface $entityManager,
        TicketRepository $ticketRepository,
        Registry $workflowRegistry,
    ) {
        $this->logger = $incomeLogger;
        $this->entityManager = $entityManager;
        $this->ticketRepository = $ticketRepository;
        $this->workflowRegistry = $workflowRegistry;
    }

    public function __invoke(TicketCreated $message): void
    {
        $ticket = $this->ticketRepository->find($message->getTicketId());
        if (TicketState::REGISTERED !== $ticket->getState()) {
            $this->logger->warning('Ticket already processed.', ['id' => $ticket->getId()]);

            return;
        }

        $workflow = $this->workflowRegistry->get($ticket);

        try {
            if ($workflow->can($ticket, 'pay')) {
                $workflow->apply($ticket, 'pay');
            } elseif ($workflow->can($ticket, 'cancel')) {
                $workflow->apply($ticket, 'cancel');
            }
        } catch (\Throwable $exception) {
            $workflow->apply($ticket, 'cancel', ['message' => $exception->getMessage()]);
        }

        $this->entityManager->flush();
    }
}
