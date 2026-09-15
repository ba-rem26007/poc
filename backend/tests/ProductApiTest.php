<?php

namespace App\Tests;

use App\Entity\Product;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ProductApiTest extends WebTestCase
{
    public function testGetProductsCollection(): void
    {
        $client = static::createClient();

        $container = static::getContainer();
        $entityManager = $container->get('doctrine')->getManager();

        $schemaTool = new \Doctrine\ORM\Tools\SchemaTool($entityManager);
        $metadata = $entityManager->getMetadataFactory()->getAllMetadata();
        $schemaTool->dropSchema($metadata);
        $schemaTool->createSchema($metadata);

        $product = new Product();
        $product->setName('Test Keyboard');
        $product->setPrice(49.99);
        $product->setIsAvailable(true);
        $entityManager->persist($product);
        $entityManager->flush();

        $client->request('GET', '/api/products', [], [], ['HTTP_ACCEPT' => 'application/ld+json']);

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        $content = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('member', $content);
        $this->assertNotEmpty($content['member']);
    }
}
