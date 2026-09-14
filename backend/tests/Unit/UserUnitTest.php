<?php

namespace App\Tests\Unit;

use App\Entity\User;
use PHPUnit\Framework\TestCase;

class UserUnitTest extends TestCase
{
    public function testUserInitialValues(): void
    {
        $user = new User();

        $this->assertNull($user->getId());
        $this->assertContains('ROLE_USER', $user->getRoles());
    }

    public function testUserGettersAndSetters(): void
    {
        $user = new User();

        $user->setEmail('admin@company.com');
        $this->assertEquals('admin@company.com', $user->getEmail());
        $this->assertEquals('admin@company.com', $user->getUserIdentifier());

        $user->setPassword('hashed_password_123');
        $this->assertEquals('hashed_password_123', $user->getPassword());

        $user->setRoles(['ROLE_ADMIN']);
        $roles = $user->getRoles();
        $this->assertContains('ROLE_ADMIN', $roles);
        $this->assertContains('ROLE_USER', $roles);
    }
}
