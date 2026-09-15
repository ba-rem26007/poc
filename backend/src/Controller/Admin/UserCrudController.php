<?php

namespace App\Controller\Admin;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserCrudController extends AbstractCrudController
{
    public function __construct(private UserPasswordHasherInterface $passwordHasher)
    {
    }

    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield EmailField::new('email');
        yield TextField::new('fullName', 'Full Name');

        $roles = [
            'User' => 'ROLE_USER',
            'Manager' => 'ROLE_MANAGER',
            'Admin' => 'ROLE_ADMIN',
        ];
        yield ChoiceField::new('roles')
            ->setChoices($roles)
            ->allowMultipleChoices();

        yield TextField::new('password')
            ->setFormType(PasswordType::class)
            ->onlyOnForms()
            ->setRequired($pageName === 'new');
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $this->hashUserPassword($entityInstance);
        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $this->hashUserPassword($entityInstance);
        parent::updateEntity($entityManager, $entityInstance);
    }

    private function hashUserPassword($entityInstance): void
    {
        if (!$entityInstance instanceof User) {
            return;
        }

        $plainPassword = $entityInstance->getPassword();
        if (!empty($plainPassword) && !str_starts_with($plainPassword, '$2y$') && !str_starts_with($plainPassword, '$argon2')) {
            $hashedPassword = $this->passwordHasher->hashPassword($entityInstance, $plainPassword);
            $entityInstance->setPassword($hashedPassword);
        }
    }
}
