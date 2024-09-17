<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use App\Entity\ContactMessage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Psr\Log\LoggerInterface;

class ContactController extends AbstractController
{
    #[Route('/contact', name: 'app_contact')]
    public function contact(
        Request $request,
        MailerInterface $mailer,
        EntityManagerInterface $entityManager,
        LoggerInterface $logger
    ): Response {
        if ($request->isMethod('POST')) {
            // Récupérer les données du formulaire
            $email = $request->request->get('email');
            $title = $request->request->get('title');
            $message = $request->request->get('message');

            // Vérification des champs vides ou nuls
            if (!$email || !$title || !$message) {
                $this->addFlash('error', 'Tous les champs sont requis.');
                return $this->redirectToRoute('app_contact');
            }

            // Créer un message de contact pour l'enregistrer dans la base de données
            $contactMessage = new ContactMessage();
            $contactMessage->setEmail($email);
            $contactMessage->setTitle($title);
            $contactMessage->setMessage($message);
            $entityManager->persist($contactMessage);
            $entityManager->flush();

            // Envoyer l'e-mail à l'équipe support
            $emailMessage = (new Email())
                ->from('support@kocinaspeed.fr')
                ->replyTo($email) // Permettre de répondre directement à l'expéditeur
                ->to('support@kocinaspeed.fr')
                ->subject($title)
                ->html(
                    '<p><strong>Email:</strong> ' . htmlspecialchars($email) . '</p>' .
                        '<p>' . nl2br(htmlspecialchars($message)) . '</p>'
                );

            try {
                $mailer->send($emailMessage);
                // Message flash de confirmation
                $this->addFlash('success', 'Votre message a bien été envoyé à l\'équipe support. Nous vous répondrons dans les plus brefs délais.');
            } catch (TransportExceptionInterface $e) {
                // Gérer l'erreur en enregistrant le message dans les logs
                $logger->error('Erreur lors de l\'envoi de l\'email de contact : ' . $e->getMessage());
                // Message flash d'erreur
                $this->addFlash('error', 'Une erreur est survenue lors de l\'envoi de votre message. Veuillez réessayer ultérieurement.');
            }

            return $this->redirectToRoute('app_contact');
        }

        return $this->render('contact/index.html.twig');
    }
}
