<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class ProfilController extends AbstractController
{
    #[Route('/profil/{id}', name: 'page_profil')]
    public function profil(int $id, EntityManagerInterface $entityManager): Response
    {
        $user = $entityManager->getRepository(User::class)->findOneBy(['id' => $id]);

        if (!$user) {
            throw $this->createNotFoundException('User not found');
        }

        $isCurrentUser = $this->getUser() && $this->getUser()->getId() === $id;

        return $this->render('profil/profil.html.twig', [
            'user' => $user,
            'isCurrentUser' => $isCurrentUser,
        ]);
    }
}