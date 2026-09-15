<?php

namespace App\Tests;

use App\Entity\User;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ProfileApiTest extends WebTestCase
{
    private function initDatabase($entityManager, $container): void
    {
        $schemaTool = new SchemaTool($entityManager);
        $metadata = $entityManager->getMetadataFactory()->getAllMetadata();
        $schemaTool->dropSchema($metadata);
        $schemaTool->createSchema($metadata);

        $hasher = $container->get('security.user_password_hasher');

        $admin = new User();
        $admin->setEmail('admin@example.com');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPassword($hasher->hashPassword($admin, 'admin123'));
        $entityManager->persist($admin);

        $user = new User();
        $user->setEmail('user@example.com');
        $user->setRoles(['ROLE_USER']);
        $user->setPassword($hasher->hashPassword($user, 'user123'));
        $entityManager->persist($user);

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

    public function testGetAndUpdateProfile(): void
    {
        $client = static::createClient();
        $container = static::getContainer();
        $entityManager = $container->get('doctrine')->getManager();

        $this->initDatabase($entityManager, $container);

        $token = $this->getJwtToken($client, 'user@example.com', 'user123');
        $this->assertNotEmpty($token);

        // GET /api/me
        $client->request('GET', '/api/me', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
            'HTTP_ACCEPT' => 'application/json',
        ]);

        $this->assertResponseIsSuccessful();
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals('user@example.com', $data['email']);

        // PUT /api/me
        $client->request('PUT', '/api/me', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'fullName' => 'Test User Fullname',
            'isTwoFactorEnabled' => true,
        ]));

        $this->assertResponseIsSuccessful();
        $dataUpdate = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals('Test User Fullname', $dataUpdate['fullName']);
        $this->assertTrue($dataUpdate['isTwoFactorEnabled']);
    }

    public function testPasswordResetFlow(): void
    {
        $client = static::createClient();
        $container = static::getContainer();
        $entityManager = $container->get('doctrine')->getManager();

        $this->initDatabase($entityManager, $container);

        // 1. Request password reset
        $client->request('POST', '/api/password_reset/request', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode(['email' => 'admin@example.com']));

        $this->assertResponseIsSuccessful();
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('resetToken', $data);
        $resetToken = $data['resetToken'];

        // 2. Reset password using token
        $client->request('POST', '/api/password_reset/reset', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'token' => $resetToken,
            'newPassword' => 'newadmin123',
        ]));

        $this->assertResponseIsSuccessful();
        $dataReset = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals('Password reset successfully', $dataReset['message']);

        // 3. Verify login with new password
        $token = $this->getJwtToken($client, 'admin@example.com', 'newadmin123');
        $this->assertNotEmpty($token);
    }
}
