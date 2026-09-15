<?php

namespace App\State;

use ApiPlatform\Doctrine\Common\State\PersistProcessor;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\User;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserPasswordHasherProcessor implements ProcessorInterface
{
    public function __construct(
        #[Autowire(service: PersistProcessor::class)]
        private ProcessorInterface $persistProcessor,
        private UserPasswordHasherInterface $passwordHasher
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        if ($data instanceof User && $data->getPassword()) {
            if (!str_starts_with($data->getPassword(), '$2y$') && !str_starts_with($data->getPassword(), '$argon2')) {
                $hashedPassword = $this->passwordHasher->hashPassword($data, $data->getPassword());
                $data->setPassword($hashedPassword);
            }
        }

        return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
    }
}
