<?php

namespace App\Controller;

use App\Entity\Products;
use App\Repository\ProductsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Route("/api", name="api")
 */
class ProductsController extends AbstractController
{

    /**
     * @param ProductsRepository $productRepository
     * @param SerializerInterface $serializer
     * @return JsonResponse
     */
    #[Route('/products', name: 'getProducts', methods: ['GET'])]
    public function getProducts(ProductsRepository $productRepository, SerializerInterface $serializer): JsonResponse
    {
        $productsList = $productRepository->findAll();
        $jsonProductsList = $serializer->serialize($productsList, 'json');
        return new JsonResponse($jsonProductsList, Response::HTTP_OK, [], true);
    }

    /**
     * @param int $id
     * @param SerializerInterface $serializer
     * @param ProductsRepository $productRepository
     * @return JsonResponse
     */
    #[Route('/products/{id}', name: 'detailProduct', methods: ['GET'])]
    public function getDetailProduct(int $id, SerializerInterface $serializer, ProductsRepository $productRepository): JsonResponse
    {
        $product = $productRepository->find($id);
        if ($product) {
            $jsonProduct = $serializer->serialize($product, 'json');
            return new JsonResponse($jsonProduct, Response::HTTP_OK, [], true);
        }
        return new JsonResponse('Product not found', Response::HTTP_NOT_FOUND);
    }

    /**
     * @param Products $products
     * @param EntityManagerInterface $em
     * @return JsonResponse
     */
    #[Route('/products/{id}', name: 'deleteProduct', methods: ['DELETE'])]
    public function deleteProduct(Products $products, EntityManagerInterface $em): JsonResponse
    {

        try {
            $em->remove($products);
            $em->flush();
            return new JsonResponse('Product deleted successfully', Response::HTTP_NO_CONTENT);
        } catch (\Exception $e) {
            return new JsonResponse('Failed to delete product', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/products', name:"createProduct", methods: ['POST'])]
    public function createProduct(Request $request, SerializerInterface $serializer, EntityManagerInterface $em): JsonResponse
    {

        $product = $serializer->deserialize($request->getContent(), Products::class, 'json');

        if($product){
            $em->persist($product);
            $em->flush();

            $jsonProduct = $serializer->serialize($product, 'json');

            return new JsonResponse($jsonProduct, Response::HTTP_CREATED, [], true);
        } else {
            return new JsonResponse('Can\'t create product', Response::HTTP_CREATED, [], true);
        }
    }

    #[Route('/products/{id}', name:"updateProduct", methods:['PUT'])]
    public function updateProduct(Request $request, SerializerInterface $serializer, Products $currentProduct, EntityManagerInterface $em): JsonResponse
    {

        $updatedProduct = $serializer->deserialize($request->getContent(),
            Products::class,
            'json',
            [AbstractNormalizer::OBJECT_TO_POPULATE => $currentProduct]);

        $em->persist($updatedProduct);

        try {
            $em->flush();
            $jsonProduct = $serializer->serialize($updatedProduct, 'json');
            return new JsonResponse($jsonProduct, Response::HTTP_OK, [], true);
        } catch (\Exception $e) {
            return new JsonResponse('Failed to update product', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
