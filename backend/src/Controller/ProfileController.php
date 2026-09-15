<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api')]
class ProfileController extends AbstractController
{
    #[Route('/me', name: 'api_me_get', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function getProfile(): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        return $this->json([
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'fullName' => $user->getFullName(),
            'roles' => $user->getRoles(),
            'isTwoFactorEnabled' => $user->isTwoFactorEnabled(),
        ]);
    }

    #[Route('/me', name: 'api_me_update', methods: ['PUT', 'PATCH'])]
    #[IsGranted('ROLE_USER')]
    public function updateProfile(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): JsonResponse {
        /** @var User $user */
        $user = $this->getUser();
        $data = json_decode($request->getContent(), true) ?? [];

        if (isset($data['fullName'])) {
            $user->setFullName($data['fullName']);
        }

        if (isset($data['isTwoFactorEnabled'])) {
            $user->setIsTwoFactorEnabled((bool) $data['isTwoFactorEnabled']);
            if ($user->isTwoFactorEnabled() && !$user->getTwoFactorSecret()) {
                $user->setTwoFactorSecret(bin2hex(random_bytes(16)));
            }
        }

        if (!empty($data['newPassword'])) {
            if (!empty($data['currentPassword'])) {
                if (!$passwordHasher->isPasswordValid($user, $data['currentPassword'])) {
                    return $this->json(['error' => 'Current password is incorrect'], Response::HTTP_BAD_REQUEST);
                }
            }
            $hashedPassword = $passwordHasher->hashPassword($user, $data['newPassword']);
            $user->setPassword($hashedPassword);
        }

        $entityManager->flush();

        return $this->json([
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'fullName' => $user->getFullName(),
            'roles' => $user->getRoles(),
            'isTwoFactorEnabled' => $user->isTwoFactorEnabled(),
            'message' => 'Profile updated successfully'
        ]);
    }

    #[Route('/password_reset/request', name: 'api_password_reset_request', methods: ['POST'])]
    public function requestPasswordReset(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? [];
        $email = $data['email'] ?? null;

        if (!$email) {
            return $this->json(['error' => 'Email is required'], Response::HTTP_BAD_REQUEST);
        }

        $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
        if ($user) {
            $token = bin2hex(random_bytes(20));
            $user->setPasswordResetToken($token);
            $user->setPasswordResetExpiresAt((new \DateTimeImmutable())->modify('+1 hour'));
            $entityManager->flush();

            $responseData = ['message' => 'If the email exists, a reset link has been dispatched'];

            // Only expose token in non-production environments (test/dev) for automated testing convenience
            $env = $this->getParameter('kernel.environment');
            if ($env === 'test' || $env === 'dev') {
                $responseData['resetToken'] = $token;
            }

            return $this->json($responseData);
        }

        return $this->json(['message' => 'If the email exists, a reset link has been dispatched']);
    }

    #[Route('/password_reset/reset', name: 'api_password_reset_reset', methods: ['POST'])]
    public function resetPassword(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): JsonResponse {
        $data = json_decode($request->getContent(), true) ?? [];
        $token = $data['token'] ?? null;
        $newPassword = $data['newPassword'] ?? null;

        if (!$token || !$newPassword) {
            return $this->json(['error' => 'Token and newPassword are required'], Response::HTTP_BAD_REQUEST);
        }

        /** @var User|null $user */
        $user = $entityManager->getRepository(User::class)->findOneBy(['passwordResetToken' => $token]);

        if (!$user || $user->getPasswordResetExpiresAt() < new \DateTimeImmutable()) {
            return $this->json(['error' => 'Invalid or expired reset token'], Response::HTTP_BAD_REQUEST);
        }

        $user->setPassword($passwordHasher->hashPassword($user, $newPassword));
        $user->setPasswordResetToken(null);
        $user->setPasswordResetExpiresAt(null);
        $entityManager->flush();

        return $this->json(['message' => 'Password reset successfully']);
    }
}
