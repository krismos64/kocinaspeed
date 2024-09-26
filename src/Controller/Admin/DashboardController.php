<?php

namespace App\Controller\Admin;

use App\Entity\Recipe;
use App\Entity\Review;
use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;

class DashboardController extends AbstractDashboardController
{
    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        // Rediriger vers le CRUD des recettes par défaut
        $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);
        return $this->redirect($adminUrlGenerator->setController(RecipeCrudController::class)->generateUrl());
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Kocinaspeed Administration');
    }

    public function configureMenuItems(): iterable
    {
        // Lien "Retour au site Kocinaspeed" sous forme de bouton
        yield MenuItem::linkToUrl('Retour au site Kocinaspeed', 'fa fa-arrow-left', $this->generateUrl('app_home'))
            ->setCssClass('btn btn-primary'); // Ajouter des classes CSS pour le style de bouton

        yield MenuItem::linkToDashboard('Tableau de bord', 'fa fa-home');

        yield MenuItem::section('Gestion des recettes');
        yield MenuItem::linkToCrud('Recettes', 'fa fa-utensils', Recipe::class);

        yield MenuItem::section('Gestion des avis');
        yield MenuItem::linkToCrud('Avis', 'fa fa-comments', Review::class);

        yield MenuItem::section('Gestion des utilisateurs');
        yield MenuItem::linkToCrud('Utilisateurs', 'fa fa-user', User::class);
    }
}
