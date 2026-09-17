<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class DeployController extends AbstractController
{
    #[Route('/api/deploy_webhook', name: 'api_deploy_webhook', methods: ['POST'])]
    public function triggerDeploy(Request $request): JsonResponse
    {
        $expectedSecret = $_ENV['DEPLOY_WEBHOOK_SECRET'] ?? getenv('DEPLOY_WEBHOOK_SECRET') ?: 'default_deploy_secret_key_123';
        $providedSecret = $request->headers->get('X-Deploy-Token') ?? $request->query->get('secret');

        if (!$providedSecret || !hash_equals($expectedSecret, $providedSecret)) {
            return $this->json(['error' => 'Invalid deploy token'], Response::HTTP_FORBIDDEN);
        }

        $projectDir = $this->getParameter('kernel.project_dir') . '/..';
        $deployScript = $projectDir . '/deploy.sh';

        if (file_exists($deployScript)) {
            // Execute deploy.sh asynchronously or synchronously in background
            $command = sprintf('nohup bash %s > /dev/null 2>&1 &', escapeshellarg($deployScript));
            exec($command);

            return $this->json([
                'message' => 'Deployment triggered successfully!',
                'timestamp' => (new \DateTimeImmutable())->format(\DateTimeInterface::ATOM)
            ]);
        }

        return $this->json(['error' => 'deploy.sh script not found'], Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    #[Route('/admin/trigger_redeploy', name: 'admin_trigger_redeploy', methods: ['POST', 'GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function adminRedeploy(): Response
    {
        $projectDir = $this->getParameter('kernel.project_dir') . '/..';
        $deployScript = $projectDir . '/deploy.sh';

        if (file_exists($deployScript)) {
            $command = sprintf('nohup bash %s > /dev/null 2>&1 &', escapeshellarg($deployScript));
            exec($command);
            $this->addFlash('success', '🚀 Redéploiement déclenché avec succès !');
        } else {
            $this->addFlash('danger', 'Impossible de trouver deploy.sh');
        }

        return $this->redirectToRoute('admin');
    }
}
