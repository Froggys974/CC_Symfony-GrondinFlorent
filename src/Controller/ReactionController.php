<?php
namespace App\Controller;

use App\Entity\Post;
use App\Entity\Reaction;
use App\Entity\User;
use App\Enum\ReactionType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class ReactionController extends AbstractController
{
    private $hub;

    public function __construct(HubInterface $hub)
    {
        $this->hub = $hub;
    }

    #[Route('/post/{id}/like', name: 'post_like', methods: ['POST'])]

    public function likePost(Post $post, EntityManagerInterface $entityManager): JsonResponse
    {
        $user = $this->getUser();

        if (!$user) {
            return new JsonResponse(['message' => 'Unauthorized'], 401);
        }

        $like = $entityManager->getRepository(Reaction::class)->findOneBy([
            'post' => $post,
            'user' => $user,
            'reactionType' => ReactionType::LIKE
        ]);

        if ($like) {
            $entityManager->remove($like);
            $entityManager->flush();

            // $update = new Update(
            //     'post/' . $post->getId(), 
            //     json_encode([
            //         'message' => 'Like removed',
            //         'likesCount' => $post->getLikesCount(),
            //     ])
            // );
            // $this->hub->publish($update);

            return new JsonResponse(['message' => 'Like removed', 'likesCount' => $post->getLikesCount()]);
        }

        $like = new Reaction();
        $like->setPost($post);
        $like->setUser($user);
        $like->setReactionType(ReactionType::LIKE);

        $entityManager->persist($like);
        $entityManager->flush();

        // $update = new Update(
        //     'post/' . $post->getId(), 
        //     json_encode([
        //         'message' => 'Like added',
        //         'likesCount' => $post->getLikesCount(),
        //     ])
        // );
        // $this->hub->publish($update);

        return new JsonResponse(['message' => 'Like added', 'likesCount' => $post->getLikesCount()]);
    }
}
