<?php

namespace App\Controller;

use App\Entity\Products;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @Route("/api", name="api")
 */
class UserController extends AbstractController
{

    #[Route('/users', name: 'update_current_user', methods: ['PUT'])]
    public function updateCurrentUser(Request $request, UserRepository $userRepository, EntityManagerInterface $entityManager, SerializerInterface $serializer): Response
    {
        // Retrieve the current user from the security context
        $currentUser = $this->getUser();

        // Retrieve the request payload and deserialize it into an associative array
        $data = json_decode($request->getContent(), true);

        // Update the user information based on the request payload
        $currentUser->setLogin($data['login'] ?? $currentUser->getLogin());
        $currentUser->setEmail($data['email'] ?? $currentUser->getEmail());
        $currentUser->setFirstname($data['firstname'] ?? $currentUser->getFirstname());
        $currentUser->setLastname($data['lastname'] ?? $currentUser->getLastname());

        // Persist the changes to the database
        $entityManager->flush();

        $jsonProduct = $serializer->serialize($currentUser, 'json', ['groups' => ['conference:list', 'conference:item']]);
        return new JsonResponse($jsonProduct, Response::HTTP_OK, [], true);
    }

    #[Route('/users', name: 'display_user', methods: ['GET'])]
    public function displayUser(UserRepository $userRepository, SerializerInterface $serializer): JsonResponse
    {
        $user = $userRepository->find($this->getUser()->getId());

        if ($user) {
            $jsonUser = $serializer->serialize($user, 'json');
            return new JsonResponse($jsonUser, Response::HTTP_OK, [], true);
        }
        return new JsonResponse('User not authentified', Response::HTTP_NOT_FOUND);
    }

}
