<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use App\Repository\ClientLogRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ClientLogRepository::class)]
#[ApiResource(
    normalizationContext: ['groups' => ['log:read']],
    denormalizationContext: ['groups' => ['log:write']],
    operations: [
        new GetCollection(),
        new Post()
    ]
)]
class ClientLog
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['log:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Groups(['log:read', 'log:write'])]
    private ?string $message = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['log:read', 'log:write'])]
    private ?string $stackTrace = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['log:read', 'log:write'])]
    private ?string $url = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['log:read', 'log:write'])]
    private ?string $userAgent = null;

    #[ORM\Column]
    #[Groups(['log:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(string $message): static
    {
        $this->message = $message;

        return $this;
    }

    public function getStackTrace(): ?string
    {
        return $this->stackTrace;
    }

    public function setStackTrace(?string $stackTrace): static
    {
        $this->stackTrace = $stackTrace;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): static
    {
        $this->url = $url;

        return $this;
    }

    public function getUserAgent(): ?string
    {
        return $this->userAgent;
    }

    public function setUserAgent(?string $userAgent): static
    {
        $this->userAgent = $userAgent;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
