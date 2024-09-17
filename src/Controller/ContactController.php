<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use App\Repository\ContactMessageRepository;
use App\Entity\ContactMessage;
use Doctrine\ORM\EntityManagerInterface;

class ContactController extends AbstractController
{
    #[Route('/contact', name: 'app_contact')]
    public function contact(Request $request, MailerInterface $mailer, EntityManagerInterface $entityManager): Response
    {
        if ($request->isMethod('POST')) {
            // Récupérer les données du formulaire
            $email = $request->request->get('email');
            $subject = $request->request->get('subject');
            $message = $request->request->get('message');

            // Créer un message de contact pour l'enregistrer dans la base de données
            $contactMessage = new ContactMessage();
            $contactMessage->setEmail($email);
            $contactMessage->setSubject($subject);
            $contactMessage->setMessage($message);
            $entityManager->persist($contactMessage);
            $entityManager->flush();

            // Envoyer l'e-mail à l'équipe support
            $emailMessage = (new Email())
                ->from($email)
                ->to('support@kocinaspeed.fr')
                ->subject($subject)
                ->html("<p><strong>Email:</strong> $email</p><p>$message</p>");

            $mailer->send($emailMessage);

            // Message flash de confirmation
            $this->addFlash('success', 'Votre message a bien été envoyé à l\'équipe support. Nous vous répondrons dans les plus brefs délais.');

            return $this->redirectToRoute('app_contact');
        }

        return $this->render('contact/index.html.twig');
    }
}
