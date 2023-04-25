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

        // Get the authenticated user from the request
        $user = $this->getUser();

        if (!$user) {
            return new JsonResponse('User not authenticated', Response::HTTP_UNAUTHORIZED);
        }

        // Get the product with the given ID from the database
        $product = $productRepository->find($productId);

        if (!$product) {
            return new JsonResponse('Product not found', Response::HTTP_NOT_FOUND);
        }

        $cart = $cartsRepository->findOneBy(['userId' => $user]);

        if (!$cart) {
            $cart = new Carts();
            $cart->setUserId($user);
            $em->persist($cart);
        }

        // Add the product to the user's cart
        $cart->addProductId($product);
        $em->flush();

        // Return a success response
        return new JsonResponse('Product added to cart');
    }

    #[Route('/carts/{productId}', name:"deleteProductFromCart", methods: ['DELETE'])]
    public function deleteProductFromCart(int $productId, SerializerInterface $serializer, EntityManagerInterface $em, ProductsRepository $productRepository, CartsRepository $cartsRepository): JsonResponse
    {
        // Get the authenticated user from the request
        $user = $this->getUser();

        if (!$user) {
            return new JsonResponse('User not authenticated', Response::HTTP_UNAUTHORIZED);
        }

        $product = $productRepository->find($productId);

        if (!$product) {
            return new JsonResponse('Product not found', Response::HTTP_NOT_FOUND);
        }

        $cart = $cartsRepository->findOneBy(['userId' => $user]);

        if (!$cart) {
            return new JsonResponse('User cart not found', Response::HTTP_NOT_FOUND);
        }

        $products = $cart->getProductId();

        foreach($products as $s){
            if(!($s->getId() == $productId)){
                return new JsonResponse('Product '.$productId.' is not in the cart', Response::HTTP_OK);
            }
        }

        $cart->removeProductId($product);
        $em->flush();

        return new JsonResponse('Product removed from cart');
    }

    #[Route('/carts', name:'getCartState', methods: ['GET'])]
    public function getCartState(ProductsRepository $productRepository, CartsRepository $cartsRepository, SerializerInterface $serializer): JsonResponse
    {

        $user = $this->getUser();

        if (!$user) {
            return new JsonResponse('User not authenticated', Response::HTTP_UNAUTHORIZED);
        }

        $cart = $cartsRepository->findOneBy(['userId' => $user]);

        if (!$cart) {
            return new JsonResponse('Cart not found', Response::HTTP_NOT_FOUND);
        }

        $productIds = $cart->getProductId();

        $jsonProductsList = $serializer->serialize($productIds, 'json');
        return new JsonResponse($jsonProductsList, Response::HTTP_OK, [], true);
    }

    #[Route('/carts/validate/', name: 'validate_cart', methods: ['POST'])]
    public function validateCart(EntityManagerInterface $em, CartsRepository $cartsRepository): JsonResponse
    {
        // Get the authenticated user from the request
        $user = $this->getUser();

        if (!$user) {
            return new JsonResponse('User not authenticated', Response::HTTP_UNAUTHORIZED);
        }

        // Find the user's shopping cart in the database
        $cart = $cartsRepository->findOneBy(['userId' => $user]);

        if (!$cart) {
            return new JsonResponse('User cart not found', Response::HTTP_NOT_FOUND);
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

        // Remove the products from the cart
        $cart->getProductId()->clear();

        // Persist the new order and updated cart in the database
        $em->persist($order);
        $em->persist($cart);
        $em->flush();

        return new JsonResponse('Cart validated and converted to an order');
    }
}
