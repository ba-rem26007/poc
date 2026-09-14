<?php

namespace App\Tests\Unit;

use App\Entity\Product;
use PHPUnit\Framework\TestCase;

class ProductUnitTest extends TestCase
{
    public function testProductInitialValues(): void
    {
        $product = new Product();

        $this->assertNull($product->getId());
        $this->assertTrue($product->isAvailable());
        $this->assertInstanceOf(\DateTimeImmutable::class, $product->getCreatedAt());
    }

    public function testProductGettersAndSetters(): void
    {
        $product = new Product();

        $product->setName('Gaming Keyboard');
        $this->assertEquals('Gaming Keyboard', $product->getName());

        $product->setDescription('RGB mechanical switches');
        $this->assertEquals('RGB mechanical switches', $product->getDescription());

        $product->setPrice(149.99);
        $this->assertEquals(149.99, $product->getPrice());

        $product->setIsAvailable(false);
        $this->assertFalse($product->isAvailable());

        $now = new \DateTimeImmutable('2026-01-01 10:00:00');
        $product->setCreatedAt($now);
        $this->assertEquals($now, $product->getCreatedAt());
    }
}
