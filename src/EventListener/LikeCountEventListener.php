<?php
namespace App\EventListener;

use App\Entity\Post;
use App\Entity\Reaction;
use App\Enum\ReactionType;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;

#[AsEntityListener(event: Events::postPersist, method: 'postPersist', entity: Reaction::class)]
#[AsEntityListener(event: Events::postRemove, method: 'postRemove', entity: Reaction::class)]
class LikeCountEventListener
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function postPersist(Reaction $reaction, LifecycleEventArgs $args): void
    {
        $this->updateLikeCount($reaction, 1);
    }

    public function postRemove(Reaction $reaction, LifecycleEventArgs $args): void
    {
        $this->updateLikeCount($reaction, -1);
    }

    private function updateLikeCount(Reaction $reaction, int $count): void
    {
        $post = $reaction->getPost();
        if ($reaction->getReactionType() === ReactionType::LIKE) {
            $post->setLikesCount($post->getLikesCount() + $count);
            $this->entityManager->persist($post);
            $this->entityManager->flush();
        }
    }
}