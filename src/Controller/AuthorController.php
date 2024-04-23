<?php

namespace App\Controller;

use App\Entity\Author;
use App\Repository\AuthorRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @Route("/api/author", name="author_api")
 */
class AuthorController extends AbstractController
{
    /**
     * @Route("/", name="index", methods={"GET"})
     */
    #[Route('/author', name: 'app_author')]
    public function index(AuthorRepository $authorRepository, SerializerInterface $serializer): Response
    {
        $authors = $authorRepository->findAll();
        $data = $serializer->serialize($authors, 'json');

        return new Response($data, 200, ['Content-Type' => 'application/json']);
    }

    /**
     * @Route("/", name="create", methods={"POST"})
     */
    public function create(Request $request, EntityManagerInterface $entityManager, SerializerInterface $serializer): Response
    {
        $requestData = $request->getContent();

        $author = $serializer->deserialize($requestData, Author::class, 'json');

        if (!$author->getName() || !$author->getEmail()) {
            return new JsonResponse(['error' => 'Missing required fields'], 400);
        }

        $entityManager->persist($author);
        $entityManager->flush();

        $data = $serializer->serialize($author, 'json');

        return new JsonResponse(['message' => 'Author created!', 'author' => json_decode($data)], 201);
    }

    /**
     * @Route("/{id}", name="update", methods={"PUT"})
     */
    public function update(Author $author, Request $request, EntityManagerInterface $entityManager, SerializerInterface $serializer): Response
    {
        $requestData = $request->getContent();
        $updatedAuthor = $serializer->deserialize($requestData, Author::class, 'json');

        $author->setName($updatedAuthor->getName());
        $author->setEmail($updatedAuthor->getEmail());

        $entityManager->flush();
        $data = $serializer->serialize($author, 'json');


        return new JsonResponse(['message' => 'Author updated!', 'author' => json_decode($data)], 200);

    }

    /**
     * @Route("/{id}", name="delete", methods={"DELETE"})
     */
    public function delete(Author $author, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($author);
        $entityManager->flush();

        return new Response('Author deleted!', 200);
    }

    /**
     * @Route("/search/{id}", name="search_by_id", methods={"GET"})
     */
    public function findById(AuthorRepository $authorRepository, int $id, SerializerInterface $serializer): Response
    {
        $author = $authorRepository->find($id);

        if (!$author) {
            return new JsonResponse(['error' => 'Author not found'], 404);
        }

        $data = $serializer->serialize($author, 'json');

        return new Response($data, 200, ['Content-Type' => 'application/json']);
    }

}
