<?php

namespace App\Controller\Admin;

use App\Entity\User;
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
}
