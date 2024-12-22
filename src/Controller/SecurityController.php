<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Service\EmailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Uid\Uuid;


class SecurityController extends AbstractController
{

    public function __construct(
        private EntityManagerInterface $entityManager,
        private EmailService $emailService,
    ) {
    }
    
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('page_home');
        }

        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }


    #[Route(path: '/forgot', name: 'auth_forgot')]
    public function forgot(Request $request, UserRepository $userRepository): Response
    {
        $error=null;
        if ($request->isMethod('POST')) {
            $email = $request->get('_email');

            $user = $userRepository->findOneBy(['email' => $email]);

            if (!$user) {
                $this->addFlash('error', 'Pas de compte associé à cet email. Merci de vérifier votre saisie.');
                return $this->redirectToRoute('auth_forgot');
            }

            $resetToken = Uuid::v4();
            $user->setResetToken($resetToken);

            $this->entityManager->persist($user);
            $this->entityManager->flush();

            $resetUrl = $this->generateUrl('auth_reset', ['token' => $resetToken], 0);
            $subject = 'Réinitialisation de votre mot de passe';
            $this->emailService->sendEmailWithTemplate(
                $email,
                $subject,
                'emails/user/reset_password.html.twig',
                ['resetUrl' => $resetUrl, 'subject' => $subject]
            );
            dump("Email envoyé à $email");
            $this->addFlash('success', 'Un email de réinitialisation a été envoyé à l\'adresse renseignée.');

            return $this->redirectToRoute('auth_forgot');
        }

        return $this->render('security/forgot.html.twig', [
            'error' => $error,
        ]);
    }

    #[Route(path: '/register', name: 'auth_register')]
    public function register(): Response
    {
        return $this->render('security/register.html.twig');
    }

    #[Route(path: '/reset/{token}', name: 'auth_reset')]
    public function reset(Request $request, UserRepository $userRepository, string $token, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = $userRepository->findOneBy(['resetToken' => $token]);

        if (!$user) {
            $this->addFlash('error', 'Token de réinitialisation invalide.');
            return $this->redirectToRoute('auth_forgot');
        }

        if ($request->isMethod('POST')) {
            $newPassword = $request->get('_newPassword');
            $confirmPassword = $request->get('_confirm_password');


            if ($newPassword !== $confirmPassword) {
                $this->addFlash('error', 'Les mots de passe ne correspondent pas.');
                return $this->redirectToRoute('auth_reset', ['token' => $token]);
            }
            $hashedPassword = $passwordHasher->hashPassword($user, $newPassword);
            $user->setPassword($hashedPassword);
            $user->setResetToken(null);

            $this->entityManager->persist($user);
            $this->entityManager->flush();

            $this->addFlash('success', 'Votre mot de passe a été réinitialisé avec succès.');

            return $this->redirectToRoute('app_login');
        }
        return $this->render('security/reset.html.twig', [
            'token' => $token,
            'email' => $user->getEmail()
        ]);
    }
}
