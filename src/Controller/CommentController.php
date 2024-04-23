<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @Route("/api/comments", name="comment_api")
 */
class CommentController extends AbstractController
{
    /**
     * @Route("/", name="create", methods={"POST"})
     */
    public function create(Request $request, EntityManagerInterface $entityManager, SerializerInterface $serializer): Response
    {
        $requestData = $request->getContent();
        $commentData = $serializer->deserialize($requestData, Comment::class, 'json');

        if (!$commentData->getContent() || !$commentData->getPostId() || !$commentData->getAuthorId()) {
            return new JsonResponse(['error' => 'Content, post_id, and author_id are required'], 400);
        }

        $entityManager->persist($commentData);
        $entityManager->flush();

        $data = $serializer->serialize($commentData, 'json');

        return new JsonResponse(['message' => 'Comment created!', 'comment' => json_decode($data)], 201);
    }

    /**
     * @Route("/{id}", name="delete", methods={"DELETE"})
     */
    public function delete(Comment $comment, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($comment);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Comment deleted!'], 200);
    }
}
