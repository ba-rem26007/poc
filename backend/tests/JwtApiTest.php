<?php

namespace App\Tests;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class JwtApiTest extends WebTestCase
{
    public function testJwtLoginSuccessAndFailure(): void
    {
        $client = static::createClient();
        $container = static::getContainer();
        $entityManager = $container->get('doctrine')->getManager();

        $schemaTool = new \Doctrine\ORM\Tools\SchemaTool($entityManager);
        $metadata = $entityManager->getMetadataFactory()->getAllMetadata();
        $schemaTool->dropSchema($metadata);
        $schemaTool->createSchema($metadata);

        $hasher = $container->get('security.user_password_hasher');
        $user = new User();
        $user->setEmail('jwt_test@example.com');
        $user->setPassword($hasher->hashPassword($user, 'password123'));
        $entityManager->persist($user);
        $entityManager->flush();

        // Failed Login
        $client->request(
            'POST',
            '/api/login_check',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json', 'HTTP_ACCEPT' => 'application/json'],
            json_encode(['username' => 'jwt_test@example.com', 'password' => 'wrongpassword'])
        );
        $this->assertResponseStatusCodeSame(401);

        // Successful Login
        $client->request(
            'POST',
            '/api/login_check',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json', 'HTTP_ACCEPT' => 'application/json'],
            json_encode(['username' => 'jwt_test@example.com', 'password' => 'password123'])
        );
        $this->assertResponseIsSuccessful();
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('token', $data);
        $this->assertNotEmpty($data['token']);
    }

    public function testClientLogPostEndpoint(): void
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/api/client_logs',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/ld+json',
                'HTTP_ACCEPT' => 'application/ld+json'
            ],
            json_encode([
                'message' => 'Uncaught ReferenceError: foo is not defined',
                'stackTrace' => 'ReferenceError: foo is not defined at App.jsx:10',
                'url' => 'http://localhost:5173/',
                'userAgent' => 'HeadlessChrome/145.0.0.0'
            ])
        );

        $this->assertResponseStatusCodeSame(201);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals('Uncaught ReferenceError: foo is not defined', $data['message']);
    }
}
