<?php

declare(strict_types=1);

namespace App\Controller;

use App\DBAL\TicketState;
use App\Entity\Ticket;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TicketController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/ticket', name: 'ticket', methods: ['POST'])]
    public function __invoke(Request $request): JsonResponse
    {
        $content = json_decode($request->getContent(), true);
        $title = $content['title'];

        if (!is_string($title)) {
            return $this->json(['message' => 'Invalid title type.'], Response::HTTP_BAD_REQUEST);
        }

        $ticket = (new Ticket())
            ->setState(TicketState::REGISTERED)
            ->setTitle($title);

        $this->entityManager->persist($ticket);
        $this->entityManager->flush();

        return $this->json('Ticket was created', Response::HTTP_CREATED);
    }
}
