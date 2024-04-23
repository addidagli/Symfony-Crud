<?php

namespace App\Controller;

use App\Entity\Author;
use App\Entity\Post;
use App\Repository\PostRepository;
use App\Repository\AuthorRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @Route("/api/post", name="post_api")
 */
class PostController extends AbstractController
{
    /**
     * @Route("/", name="index", methods={"GET"})
     */
    public function index(PostRepository $postRepository, SerializerInterface $serializer): Response
    {
        $posts = $postRepository->findAll();
        $data = $serializer->serialize($posts, 'json');

        return new Response($data, 200, ['Content-Type' => 'application/json']);
    }

    /**
     * @Route("/", name="create", methods={"POST"})
     */

    public function create(Request $request, EntityManagerInterface $entityManager, SerializerInterface $serializer, AuthorRepository $authorRepository): Response
    {
        $requestData = $request->getContent();
        $postData = $serializer->deserialize($requestData, Post::class, 'json');

        if (!$postData->getTitle() || !$postData->getContent() || !$postData->getAuthorId()) {
            return new JsonResponse(['error' => 'Title, content, and author_id are required'], 400);
        }

        $authorId = $postData->getAuthorId();
        $author = $authorRepository->find($authorId);
        dump($author);
        dump($authorId);

        if (!$author) {
            return new JsonResponse(['error' => 'Author not found'], 404);
        }

        $post = new Post();
        $post->setTitle($postData->getTitle());
        $post->setContent($postData->getContent());
        $post->setAuthorId($authorId);

        $entityManager->persist($post);
        $entityManager->flush();

        $data = $serializer->serialize($post, 'json');

        return new JsonResponse(['message' => 'Post created!', 'post' => json_decode($data)], 201);
    }

    /**
     * @Route("/{id}", name="update", methods={"PUT"})
     */
    public function update(Post $post, Request $request, EntityManagerInterface $entityManager, SerializerInterface $serializer): Response
    {
        $requestData = $request->getContent();
        $updatedPost = $serializer->deserialize($requestData, Post::class, 'json');

        $post->setTitle($updatedPost->getTitle());
        $post->setContent($updatedPost->getContent());
        $post->setAuthor($updatedPost->getAuthor());

        $entityManager->flush();
        $data = $serializer->serialize($post, 'json');

        return new JsonResponse(['message' => 'Post updated!', 'post' => json_decode($data)], 200);
    }

    /**
     * @Route("/{id}", name="delete", methods={"DELETE"})
     */
    public function delete(Post $post, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($post);
        $entityManager->flush();

        return new Response('Post deleted!', 200);
    }

    /**
     * @Route("/search/{id}", name="search_by_id", methods={"GET"})
     */
    public function findById(PostRepository $postRepository, int $id, SerializerInterface $serializer): Response
    {
        $post = $postRepository->find($id);

        if (!$post) {
            return new JsonResponse(['error' => 'Post not found'], 404);
        }

        $data = $serializer->serialize($post, 'json');

        return new Response($data, 200, ['Content-Type' => 'application/json']);
    }
}
