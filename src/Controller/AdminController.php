<?php

namespace App\Controller;

use App\Repository\ContactMessageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractController
{
    /**
     * @Route("/admin/dashboard", name="admin_dashboard")
     */
    public function dashboard(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        return $this->render('admin/dashboard.html.twig');
    }

    #[Route('/admin/messages', name: 'app_admin_messages')]
    public function showMessages(ContactMessageRepository $messageRepository): Response
    {
        $messages = $messageRepository->findAll();
        return $this->render('admin/messages.html.twig', [
            'messages' => $messages,
        ]);
    }
}
