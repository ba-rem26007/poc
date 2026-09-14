<?php

namespace App\DataFixtures;

use App\Entity\Product;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $passwordHasher)
    {
    }

    public function load(ObjectManager $manager): void
    {
        // SuperAdmin User
        $admin = new User();
        $admin->setEmail('admin@example.com');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPassword($this->passwordHasher->hashPassword($admin, 'admin123'));
        $manager->persist($admin);

        // Regular User
        $user = new User();
        $user->setEmail('user@example.com');
        $user->setRoles(['ROLE_USER']);
        $user->setPassword($this->passwordHasher->hashPassword($user, 'user123'));
        $manager->persist($user);

        // Sample Products
        $productsData = [
            [
                'name' => 'High-Performance Laptop',
                'description' => 'Powerful 16-inch laptop with 32GB RAM and 1TB SSD.',
                'price' => 1299.99,
                'available' => true,
            ],
            [
                'name' => 'Wireless Noise-Canceling Headphones',
                'description' => 'Premium over-ear headphones with active noise cancellation.',
                'price' => 249.50,
                'available' => true,
            ],
            [
                'name' => 'Ergonomic Mechanical Keyboard',
                'description' => 'Customizable RGB mechanical keyboard with tactile switches.',
                'price' => 119.00,
                'available' => false,
            ],
            [
                'name' => '4K Ultra HD Monitor',
                'description' => '27-inch IPS display with vibrant colors and HDR support.',
                'price' => 389.95,
                'available' => true,
            ],
        ];

        foreach ($productsData as $data) {
            $product = new Product();
            $product->setName($data['name']);
            $product->setDescription($data['description']);
            $product->setPrice($data['price']);
            $product->setIsAvailable($data['available']);
            $manager->persist($product);
        }

        $manager->flush();
    }
}
