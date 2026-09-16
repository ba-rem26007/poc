<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class DocController extends AbstractController
{
    #[Route('/doc', name: 'app_doc', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function index(): Response
    {
        $docDir = $this->getParameter('kernel.project_dir') . '/doc';
        $archDoc = file_exists($docDir . '/architecture.md') ? file_get_contents($docDir . '/architecture.md') : '';
        $deployDoc = file_exists($docDir . '/deployment.md') ? file_get_contents($docDir . '/deployment.md') : '';
        $solidDoc = file_exists($docDir . '/solid.md') ? file_get_contents($docDir . '/solid.md') : '';

        return $this->render('doc/index.html.twig', [
            'architectureDoc' => $archDoc,
            'deploymentDoc' => $deployDoc,
            'solidDoc' => $solidDoc,
        ]);
    }
}
