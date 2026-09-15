<?php

namespace App\Tests;

use App\Entity\Product;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ProductSecurityApiTest extends WebTestCase
{
    private function setupDatabase($entityManager): void
    {
        $schemaTool = new \Doctrine\ORM\Tools\SchemaTool($entityManager);
        $metadata = $entityManager->getMetadataFactory()->getAllMetadata();
        $schemaTool->dropSchema($metadata);
        $schemaTool->createSchema($metadata);
    }

    public function testAnonymousUserCannotCreateProduct(): void
    {
        $client = static::createClient();
        $container = static::getContainer();
        $entityManager = $container->get('doctrine')->getManager();
        $this->setupDatabase($entityManager);

        $client->request(
            'POST',
            '/api/products',
            [],
            [],
            ['CONTENT_TYPE' => 'application/ld+json', 'HTTP_ACCEPT' => 'application/ld+json'],
            json_encode(['name' => 'Forbidden Product', 'price' => 10])
        );

        $this->assertResponseStatusCodeSame(401);
    }

    public function testAuthenticatedUserCanCreateAndUpdateProduct(): void
    {
        $client = static::createClient();
        $container = static::getContainer();
        $entityManager = $container->get('doctrine')->getManager();
        $this->setupDatabase($entityManager);

        $hasher = $container->get('security.user_password_hasher');
        $user = new User();
        $user->setEmail('user_test@example.com');
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
            json_encode(['username' => 'user_test@example.com', 'password' => 'password123'])
        );
        $this->assertResponseIsSuccessful();
        $token = json_decode($client->getResponse()->getContent(), true)['token'];

        // Create Product
        $client->request(
            'POST',
            '/api/products',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/ld+json',
                'HTTP_ACCEPT' => 'application/ld+json',
                'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
            ],
            json_encode(['name' => 'Auth Created Product', 'price' => 49.99, 'isAvailable' => true])
        );
        $this->assertResponseStatusCodeSame(201);
        $created = json_decode($client->getResponse()->getContent(), true);

        // Update (PUT)
        $client->request(
            'PUT',
            '/api/products/' . $created['id'],
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/ld+json',
                'HTTP_ACCEPT' => 'application/ld+json',
                'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
            ],
            json_encode(['name' => 'Updated Product Name', 'price' => 59.99, 'isAvailable' => false])
        );
        $this->assertResponseIsSuccessful();
        $updated = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals('Updated Product Name', $updated['name']);
        $this->assertEquals(59.99, $updated['price']);
    }

    public function testRegularUserCannotDeleteProductButAdminCan(): void
    {
        $client = static::createClient();
        $container = static::getContainer();
        $entityManager = $container->get('doctrine')->getManager();
        $this->setupDatabase($entityManager);

        $hasher = $container->get('security.user_password_hasher');

        // Regular User
        $user = new User();
        $user->setEmail('regular@test.com');
        $user->setRoles(['ROLE_USER']);
        $user->setPassword($hasher->hashPassword($user, 'user123'));
        $entityManager->persist($user);

        // Admin User
        $admin = new User();
        $admin->setEmail('admin@test.com');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPassword($hasher->hashPassword($admin, 'admin123'));
        $entityManager->persist($admin);

        // Product
        $product = new Product();
        $product->setName('Product to Delete');
        $product->setPrice(20.0);
        $entityManager->persist($product);
        $entityManager->flush();
        $productId = $product->getId();

        // 1. Regular User Login & Delete -> 403 Forbidden
        $client->request(
            'POST',
            '/api/login_check',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json', 'HTTP_ACCEPT' => 'application/json'],
            json_encode(['username' => 'regular@test.com', 'password' => 'user123'])
        );
        $this->assertResponseIsSuccessful();
        $userToken = json_decode($client->getResponse()->getContent(), true)['token'];

        $client->request(
            'DELETE',
            '/api/products/' . $productId,
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $userToken]
        );
        $this->assertResponseStatusCodeSame(403);

        // 2. Admin User Login & Delete -> 204 No Content
        $client->request(
            'POST',
            '/api/login_check',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json', 'HTTP_ACCEPT' => 'application/json'],
            json_encode(['username' => 'admin@test.com', 'password' => 'admin123'])
        );
        $this->assertResponseIsSuccessful();
        $adminToken = json_decode($client->getResponse()->getContent(), true)['token'];

        $client->request(
            'DELETE',
            '/api/products/' . $productId,
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $adminToken]
        );
        $this->assertResponseStatusCodeSame(204);
    }
}
