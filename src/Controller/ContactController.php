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

            // Générer le contenu des emails à partir des templates Twig
            $supportEmailContent = $this->renderView('emails/support_email.html.twig', [
                'email' => $email,
                'title' => $title,
                'message' => $message,
            ]);

            $confirmationEmailContent = $this->renderView('emails/confirmation_email.html.twig', [
                'title' => $title,
                'message' => $message,
            ]);

            // Envoyer l'e-mail à l'équipe support
            $supportEmail = (new Email())
                ->from('support@kocinaspeed.fr') // Votre adresse email
                ->replyTo($email) // Permettre de répondre directement à l'expéditeur
                ->to('support@kocinaspeed.fr')
                ->subject('Nouveau message de contact : ' . $title)
                ->html($supportEmailContent);

            // Envoyer l'e-mail de confirmation à l'utilisateur
            $confirmationEmail = (new Email())
                ->from('support@kocinaspeed.fr') // Votre adresse email
                ->to($email) // Adresse de l'utilisateur
                ->subject('Confirmation de réception de votre message')
                ->html($confirmationEmailContent);

            try {
                // Envoyer l'e-mail au support
                $mailer->send($supportEmail);

                // Envoyer l'e-mail de confirmation à l'utilisateur
                $mailer->send($confirmationEmail);

                // Message flash de confirmation
                $this->addFlash('success', 'Votre message a bien été envoyé. Un email de confirmation vous a été envoyé.');
            } catch (TransportExceptionInterface $e) {
                // Gérer l'erreur en enregistrant le message dans les logs
                $logger->error('Erreur lors de l\'envoi des emails : ' . $e->getMessage());

                // Message flash d'erreur
                $this->addFlash('error', 'Une erreur est survenue lors de l\'envoi de votre message. Veuillez réessayer ultérieurement.');
            }

            return $this->redirectToRoute('app_contact');
        }

        return $this->render('contact/index.html.twig');
    }
}
