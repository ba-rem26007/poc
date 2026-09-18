<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\Product;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api')]
class CheckoutController extends AbstractController
{
    #[Route('/checkout', name: 'api_checkout', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function checkout(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        $data = json_decode($request->getContent(), true) ?? [];
        $itemsData = $data['items'] ?? [];

        if (empty($itemsData)) {
            return $this->json(['error' => 'Cart items are required'], Response::HTTP_BAD_REQUEST);
        }

        $order = new Order();
        $order->setUser($user);
        $order->setStatus('PAID');
        $totalAmount = 0.0;

        foreach ($itemsData as $item) {
            $productId = $item['productId'] ?? null;
            $quantity = (int) ($item['quantity'] ?? 1);

            if (!$productId || $quantity <= 0) {
                continue;
            }

            $product = $entityManager->getRepository(Product::class)->find($productId);
            if (!$product) {
                return $this->json(['error' => sprintf('Product ID %s not found', $productId)], Response::HTTP_BAD_REQUEST);
            }

            $unitPrice = $product->getPrice();
            $lineTotal = $unitPrice * $quantity;
            $totalAmount += $lineTotal;

            $orderItem = new OrderItem();
            $orderItem->setProduct($product);
            $orderItem->setQuantity($quantity);
            $orderItem->setUnitPrice($unitPrice);

            $order->addOrderItem($orderItem);
        }

        $order->setTotalAmount($totalAmount);
        $entityManager->persist($order);
        $entityManager->flush();

        return $this->json([
            'orderId' => $order->getId(),
            'totalAmount' => $order->getTotalAmount(),
            'status' => $order->getStatus(),
            'createdAt' => $order->getCreatedAt()->format(\DateTimeInterface::ATOM),
            'message' => 'Commande validée et payée avec succès !'
        ], Response::HTTP_CREATED);
    }
}
