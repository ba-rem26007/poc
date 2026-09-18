<?php

namespace App\Tests;

use App\Entity\Product;
use App\Entity\User;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class OrderApiTest extends WebTestCase
{
    private function initDatabase($entityManager, $container): void
    {
        $schemaTool = new SchemaTool($entityManager);
        $metadata = $entityManager->getMetadataFactory()->getAllMetadata();
        $schemaTool->dropSchema($metadata);
        $schemaTool->createSchema($metadata);

        $hasher = $container->get('security.user_password_hasher');

        $user = new User();
        $user->setEmail('customer@example.com');
        $user->setRoles(['ROLE_USER']);
        $user->setPassword($hasher->hashPassword($user, 'password123'));
        $entityManager->persist($user);

        $product = new Product();
        $product->setName('Test Earbuds');
        $product->setPrice(50.00);
        $product->setIsAvailable(true);
        $entityManager->persist($product);

        $entityManager->flush();
    }

    private function getJwtToken($client, string $email, string $password): string
    {
        $client->request('POST', '/api/login_check', [], [], [
            'CONTENT_TYPE' => 'application/json'
        ], json_encode([
            'username' => $email,
            'password' => $password,
        ]));

        $data = json_decode($client->getResponse()->getContent(), true);
        return $data['token'] ?? '';
    }

    public function testCheckoutAndOrderHistory(): void
    {
        $client = static::createClient();
        $container = static::getContainer();
        $entityManager = $container->get('doctrine')->getManager();

        $this->initDatabase($entityManager, $container);

        $product = $entityManager->getRepository(Product::class)->findOneBy(['name' => 'Test Earbuds']);
        $token = $this->getJwtToken($client, 'customer@example.com', 'password123');
        $this->assertNotEmpty($token);

        // 1. Checkout
        $client->request('POST', '/api/checkout', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'items' => [
                ['productId' => $product->getId(), 'quantity' => 2]
            ]
        ]));

        $this->assertResponseStatusCodeSame(201);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(100.0, $data['totalAmount']);
        $this->assertEquals('PAID', $data['status']);

        // 2. Query Orders Collection
        $client->request('GET', '/api/orders', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
            'HTTP_ACCEPT' => 'application/ld+json',
        ]);

        $this->assertResponseIsSuccessful();
        $ordersData = json_decode($client->getResponse()->getContent(), true);
        $this->assertNotEmpty($ordersData['member']);
    }
}
