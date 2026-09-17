<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DeployWebhookTest extends WebTestCase
{
    public function testDeployWebhookWithInvalidSecretFails(): void
    {
        $client = static::createClient();
        $client->request('POST', '/api/deploy_webhook', [], [], [
            'HTTP_X_DEPLOY_TOKEN' => 'wrong_token'
        ]);

        $this->assertResponseStatusCodeSame(403);
    }

    public function testDeployWebhookWithValidSecretSucceeds(): void
    {
        $client = static::createClient();
        $secret = $_ENV['DEPLOY_WEBHOOK_SECRET'] ?? 'default_deploy_secret_key_123';

        $client->request('POST', '/api/deploy_webhook', [], [], [
            'HTTP_X_DEPLOY_TOKEN' => $secret
        ]);

        $this->assertResponseIsSuccessful();
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals('Deployment triggered successfully!', $data['message']);
    }
}
