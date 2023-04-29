<?php

namespace App\Controller;

use App\Entity\Orders;
use App\Repository\CartsRepository;
use App\Repository\OrdersRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @Route("/api", name="api")
 */
class OrdersController extends AbstractController
{

    #[Route('/orders/{id}', name: 'get_order', methods: ['GET'])]
    public function getOrder(Orders $order): JsonResponse
    {
        $data = [
            'id' => $order->getId(),
            'totalPrice' => $order->getTotalPrice(),
            'creationDate' => $order->getCreationDate(),
            'products' => [],
        ];

        foreach ($order->getProducts() as $product) {
            $data['products'][] = [
                'id' => $product->getId(),
                'name' => $product->getName(),
                'description' => $product->getDescription(),
                'photo' => $product->getPhoto(),
                'price' => $product->getPrice(),
            ];
        }

        return new JsonResponse($data);
    }

    #[Route('/orders', name: 'get_user_orders', methods: ['GET'])]
    public function getUserOrders(OrdersRepository $ordersRepository, SerializerInterface $serializer): JsonResponse
    {
        $user = $this->getUser();

        if (!$user) {
            return new JsonResponse('User not authenticated', Response::HTTP_UNAUTHORIZED);
        }

        $orders = $ordersRepository->findBy(['user_id' => $user]);

        if (!$orders) {
            return new JsonResponse('Any order has been found', Response::HTTP_NOT_FOUND);
        }

        $user_orders = [];
        foreach ($orders as $order) {
            $user_orders[] = [
                'id' => $order->getId(),
                'totalPrice' => $order->getTotalPrice(),
                'creationDate' => $order->getCreationDate(),
                'products' => $order->getProducts(),
            ];
        }

        $jsonProductsList = $serializer->serialize($user_orders, 'json');
        return new JsonResponse($jsonProductsList, Response::HTTP_OK, [], true);
    }

}
