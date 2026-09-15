<?php

namespace App\Tests;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserSecurityApiTest extends WebTestCase
{
    private function setupDatabase($entityManager): void
    {
        $schemaTool = new \Doctrine\ORM\Tools\SchemaTool($entityManager);
        $metadata = $entityManager->getMetadataFactory()->getAllMetadata();
        $schemaTool->dropSchema($metadata);
        $schemaTool->createSchema($metadata);
    }

    public function testRegularUserCannotListUsers(): void
    {
        $client = static::createClient();
        $container = static::getContainer();
        $entityManager = $container->get('doctrine')->getManager();
        $this->setupDatabase($entityManager);

        $hasher = $container->get('security.user_password_hasher');
        $user = new User();
        $user->setEmail('regular_user@example.com');
        $user->setRoles(['ROLE_USER']);
        $user->setPassword($hasher->hashPassword($user, 'password123'));
        $entityManager->persist($user);
        $entityManager->flush();

        $client->request(
            'POST',
            '/api/login_check',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json', 'HTTP_ACCEPT' => 'application/json'],
            json_encode(['username' => 'regular_user@example.com', 'password' => 'password123'])
        );
        $token = json_decode($client->getResponse()->getContent(), true)['token'];

        // Regular User tries to GET /api/users -> 403
        $client->request(
            'GET',
            '/api/users',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $token, 'HTTP_ACCEPT' => 'application/ld+json']
        );
        $this->assertResponseStatusCodeSame(403);
    }

    public function testAdminCanCreateAndManageUsers(): void
    {
        $client = static::createClient();
        $container = static::getContainer();
        $entityManager = $container->get('doctrine')->getManager();
        $this->setupDatabase($entityManager);

        $hasher = $container->get('security.user_password_hasher');
        $admin = new User();
        $admin->setEmail('admin_user@example.com');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPassword($hasher->hashPassword($admin, 'admin123'));
        $entityManager->persist($admin);
        $entityManager->flush();

        $client->request(
            'POST',
            '/api/login_check',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json', 'HTTP_ACCEPT' => 'application/json'],
            json_encode(['username' => 'admin_user@example.com', 'password' => 'admin123'])
        );
        $adminToken = json_decode($client->getResponse()->getContent(), true)['token'];

        // Admin POST /api/users
        $client->request(
            'POST',
            '/api/users',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/ld+json',
                'HTTP_ACCEPT' => 'application/ld+json',
                'HTTP_AUTHORIZATION' => 'Bearer ' . $adminToken
            ],
            json_encode([
                'email' => 'new_manager@example.com',
                'roles' => ['ROLE_MANAGER'],
                'password' => 'manager123'
            ])
        );
        $this->assertResponseStatusCodeSame(201);
        $createdUser = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals('new_manager@example.com', $createdUser['email']);
        $this->assertContains('ROLE_MANAGER', $createdUser['roles']);
    }
}
