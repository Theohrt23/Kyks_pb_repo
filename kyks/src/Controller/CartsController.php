<?php

namespace App\Controller;

use App\Entity\Carts;
use App\Entity\Orders;
use App\Entity\Products;
use App\Repository\CartsRepository;
use App\Repository\ProductsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @Route("/api", name="api")
 */
class CartsController extends AbstractController
{
    #[Route('/carts/{productId}', name:"createCarts", methods: ['POST'])]
    public function addProductToCart(int $productId, SerializerInterface $serializer, EntityManagerInterface $em, ProductsRepository $productRepository, CartsRepository $cartsRepository): JsonResponse
    {

        $user = $this->getUser();

        if (!$user) {
            return new JsonResponse(['error' => 'User not authenticated'], Response::HTTP_UNAUTHORIZED);
        }

        $product = $productRepository->find($productId);

        if (!$product) {
            return new JsonResponse(['error' => 'Product not found'], Response::HTTP_NOT_FOUND);
        }

        $cart = $cartsRepository->findOneBy(['userId' => $user]);

        if (!$cart) {
            $cart = new Carts();
            $cart->setUserId($user);
            $em->persist($cart);
        }

        try {
            $cart->addProductId($product);
            $em->flush();

            return new JsonResponse('Product added to cart', Response::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Failed to add product'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    #[Route('/carts/{productId}', name:"deleteProductFromCart", methods: ['DELETE'])]
    public function deleteProductFromCart(int $productId, SerializerInterface $serializer, EntityManagerInterface $em, ProductsRepository $productRepository, CartsRepository $cartsRepository): JsonResponse
    {
        $user = $this->getUser();

        if (!$user) {
            return new JsonResponse(['error' => 'User not authenticated'], Response::HTTP_UNAUTHORIZED);
        }

        $product = $productRepository->find($productId);

        if (!$product) {
            return new JsonResponse(['error' => 'Product not found'], Response::HTTP_NOT_FOUND);
        }

        $cart = $cartsRepository->findOneBy(['userId' => $user]);

        if (!$cart) {
            return new JsonResponse(['error' => 'User cart not found'], Response::HTTP_NOT_FOUND);
        }

        $products = $cart->getProductId();

        foreach($products as $s){
            if(($s->getId() == $productId)){
                $cart->removeProductId($product);
                $em->flush();

                return new JsonResponse('Product removed from cart', Response::HTTP_OK);
            }
        }

        return new JsonResponse(['error' => 'Product '.$productId.' is not in the cart'], Response::HTTP_OK);
    }

    #[Route('/carts', name:'getCartState', methods: ['GET'])]
    public function getCartState(ProductsRepository $productRepository, CartsRepository $cartsRepository, SerializerInterface $serializer): JsonResponse
    {

        $user = $this->getUser();

        if (!$user) {
            return new JsonResponse(['error' =>  'User not authenticated'], Response::HTTP_UNAUTHORIZED);
        }

        $cart = $cartsRepository->findOneBy(['userId' => $user]);

        if (!$cart) {
            return new JsonResponse(['error' => 'Cart not found'], Response::HTTP_NOT_FOUND);
        }

        $productIds = $cart->getProductId();

        $jsonProductsList = $serializer->serialize($productIds, 'json');
        return new JsonResponse($jsonProductsList, Response::HTTP_OK, [], true);
    }

    #[Route('/carts/validate/', name: 'validate_cart', methods: ['POST'])]
    public function validateCart(EntityManagerInterface $em, CartsRepository $cartsRepository): JsonResponse
    {

        $user = $this->getUser();

        if (!$user) {
            return new JsonResponse(['error' => 'User not authenticated'], Response::HTTP_UNAUTHORIZED);
        }

        $cart = $cartsRepository->findOneBy(['userId' => $user]);

        if (!$cart) {
            return new JsonResponse(['error' => 'User cart not found'], Response::HTTP_NOT_FOUND);
        }

        // Calculate the total price of the products in the cart
        $totalPrice = 0;

        foreach ($cart->getProductId() as $product) {
            $totalPrice += $product->getPrice();
        }

        // Create a new Orders entity and set its properties
        $order = new Orders();
        $order->setTotalPrice($totalPrice);
        $order->setCreationDate(new \DateTime());
        $order->setUserId($user);

        // Add the products from the cart to the order
        foreach ($cart->getProductId() as $product) {
            $order->addProduct($product);
        }

        if($order->getProducts()->isEmpty()){
            return new JsonResponse(['error' => 'Cart is empty'], Response::HTTP_NOT_FOUND);
        }

        // Remove the products from the cart
        $cart->getProductId()->clear();

        try {
            $em->persist($order);
            $em->persist($cart);
            $em->flush();

            return new JsonResponse('Cart validated and converted to an order', Response::HTTP_OK);

        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Order not created'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }
}
