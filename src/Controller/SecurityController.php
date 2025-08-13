<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security as SecurityBundleSecurity;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route('/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils, SecurityBundleSecurity $security): Response
    {
        // Si l'utilisateur est déjà connecté, on redirige vers l'admin ou la page d'accueil selon son rôle
        if ($this->getUser()) {
            if ($security->isGranted('ROLE_ADMIN')) {
                return $this->redirectToRoute('admin'); // EasyAdmin dashboard
            } else {
                return $this->redirectToRoute('app_home');
            }
        }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('auth/index.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    // Route admin supprimée - gérée par EasyAdmin DashboardController

    #[Route('/logout', name: 'app_logout')]
    public function logout()
    {
        throw new \Exception('This should never be reached!');
    }
}
