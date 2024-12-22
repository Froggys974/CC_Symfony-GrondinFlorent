<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\Comment;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;


#[IsGranted('ROLE_USER')]
class CommentController extends AbstractController
{
    #[Route('/post/{id}/comment', name: 'post_comment', methods: ['POST'])]
    public function comment(Post $post, Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $user = $this->getUser();

        if (!$user) {
            return new JsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $token = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('comment' . $post->getId(), $token)) {
            return new JsonResponse(['success' => false, 'message' => 'Invalid CSRF token'], 400);
        }

        $content = trim($request->request->get('content'));
        if (empty($content)) {
            return new JsonResponse(['success' => false, 'message' => 'Comment content cannot be empty'], 400);
        }

        // Filtrer le contenu pour prévenir les attaques XSS
        $sanitizedContent = htmlspecialchars($content, ENT_QUOTES, 'UTF-8');

        $comment = new Comment();
        $comment->setPost($post);
        $comment->setUser($user);
        $comment->setContent($sanitizedContent); // Utiliser le contenu filtré
        $comment->setUpdatedAt(new \DateTimeImmutable());

        $entityManager->persist($comment);
        $entityManager->flush();

        return new JsonResponse([
            'success' => true,
            'comment' => [
                'user' => [
                    'username' => $user->getUsername(),
                    'profilePicture' => $user->getProfilPicture(),
                ],
                'content' => $sanitizedContent, // Retourner le contenu filtré
                'createdAt' => $comment->getCreatedAt()->format('H:i, d M Y'),
            ],
        ]);
    }
}
