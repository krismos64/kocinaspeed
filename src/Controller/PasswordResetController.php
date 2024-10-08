<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;

class PasswordResetController extends AbstractController
{
    #[Route('/forgot-password', name: 'app_forgot_password')]
    public function forgotPassword(
        Request $request,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager,
        TokenGeneratorInterface $tokenGenerator,
        MailerInterface $mailer,
        LoggerInterface $logger
    ): Response {
        $email = $request->request->get('email');

        if ($email) {
            $user = $userRepository->findOneBy(['email' => $email]);

            if ($user) {
                // Générer un token unique
                $resetToken = $tokenGenerator->generateToken();
                $user->setResetToken($resetToken);
                $user->setResetTokenExpiry(new \DateTime('+1 hour'));
                $entityManager->flush();

                // Générer le contenu de l'email à partir du template Twig
                $emailContent = $this->renderView('emails/password_reset.html.twig', [
                    'user' => $user,
                    'resetToken' => $resetToken,
                ]);

                // Créer l'e-mail avec le contenu rendu
                $emailMessage = (new Email())
                    ->from('support@kocinaspeed.fr')
                    ->to($user->getEmail())
                    ->subject('Réinitialisation de votre mot de passe')
                    ->html($emailContent);

                try {
                    // Envoi de l'email de manière synchrone
                    $mailer->send($emailMessage);
                    $this->addFlash('success', 'Un e-mail de réinitialisation de mot de passe a été envoyé à votre adresse.');
                } catch (TransportExceptionInterface $e) {
                    // Gérer l'erreur en enregistrant le message dans les logs
                    $logger->error('Erreur lors de l\'envoi de l\'email de réinitialisation : ' . $e->getMessage());
                    $this->addFlash('error', 'Une erreur est survenue lors de l\'envoi de l\'e-mail. Veuillez réessayer plus tard.');
                }
            } else {
                $this->addFlash('error', 'Cette adresse e-mail n\'est pas enregistrée dans notre système.');
            }
        }

        return $this->render('security/forgot_password.html.twig');
    }

    #[Route('/reset-password/{token}', name: 'app_reset_password')]
    public function resetPassword(
        string $token,
        Request $request,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        $user = $userRepository->findOneBy(['resetToken' => $token]);

        // Vérification de la validité du token
        if (!$user || $user->getResetTokenExpiry() < new \DateTime()) {
            $this->addFlash('error', 'Le lien de réinitialisation est invalide ou a expiré.');
            return $this->redirectToRoute('app_forgot_password');
        }

        // Si le formulaire de réinitialisation a été soumis
        if ($request->isMethod('POST')) {
            $newPassword = $request->request->get('password');

            // Validation de la longueur du mot de passe
            if (strlen($newPassword) < 6) {
                $this->addFlash('error', 'Le mot de passe doit contenir au moins 6 caractères.');
                return $this->render('security/reset_password.html.twig', [
                    'token' => $token,
                ]);
            }

            // Réinitialisation du mot de passe
            $user->setPassword($passwordHasher->hashPassword($user, $newPassword));
            $user->setResetToken(null);
            $user->setResetTokenExpiry(null);
            $entityManager->flush();

            // Message de succès
            $this->addFlash('success', 'Votre mot de passe a été réinitialisé avec succès.');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/reset_password.html.twig', [
            'token' => $token,
        ]);
    }
}
