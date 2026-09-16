<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HealthController extends AbstractController
{
    #[Route('/api/health', name: 'api_health', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): JsonResponse
    {
        $dbConnected = false;
        try {
            $connection = $entityManager->getConnection();
            $connection->executeQuery($connection->getDatabasePlatform()->getDummySelectSQL());
            $dbConnected = true;
        } catch (\Throwable $e) {
            $dbConnected = false;
        }

        $status = $dbConnected ? Response::HTTP_OK : Response::HTTP_SERVICE_UNAVAILABLE;

        return $this->json([
            'status' => $dbConnected ? 'OK' : 'ERROR',
            'timestamp' => (new \DateTimeImmutable())->format(\DateTimeInterface::ATOM),
            'environment' => $this->getParameter('kernel.environment'),
            'checks' => [
                'database' => $dbConnected ? 'connected' : 'disconnected',
            ]
        ], $status);
    }
}
