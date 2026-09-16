<?php

namespace App\Tests;

use App\Entity\User;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RagApiTest extends WebTestCase
{
    private function initDatabase($entityManager, $container): void
    {
        $schemaTool = new SchemaTool($entityManager);
        $metadata = $entityManager->getMetadataFactory()->getAllMetadata();
        $schemaTool->dropSchema($metadata);
        $schemaTool->createSchema($metadata);

        $hasher = $container->get('security.user_password_hasher');

        $user = new User();
        $user->setEmail('user@example.com');
        $user->setRoles(['ROLE_USER']);
        $user->setPassword($hasher->hashPassword($user, 'user123'));
        $entityManager->persist($user);
        $entityManager->flush();
    }

    public function testRagAskEndpointPublic(): void
    {
        $client = static::createClient();
        $container = static::getContainer();
        $entityManager = $container->get('doctrine')->getManager();

        $this->initDatabase($entityManager, $container);

        $client->request('POST', '/api/rag/ask', [], [], [
            'CONTENT_TYPE' => 'application/json'
        ], json_encode(['question' => 'Quels sont les produits disponibles ?']));

        $this->assertResponseIsSuccessful();
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals('Quels sont les produits disponibles ?', $data['question']);
        $this->assertArrayHasKey('answer', $data);
        $this->assertArrayHasKey('source', $data);
    }
}
