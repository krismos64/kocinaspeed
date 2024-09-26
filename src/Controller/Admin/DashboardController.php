<?php

namespace App\Controller\Admin;

use App\Entity\Recipe;
use App\Entity\Review;
use App\Entity\User;
use App\Entity\ContactMessage;
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
        $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);
        return $this->redirect($adminUrlGenerator->setController(RecipeCrudController::class)->generateUrl());
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('<a href="' . $this->generateUrl('app_home') . '"><img src="/img/logo.png" alt="Logo" style="max-height: 40px; margin-right: 10px;"> Kocinaspeed</a>');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToUrl('Retour sur Kocinaspeed', 'fa fa-arrow-left', $this->generateUrl('app_home'));
        yield MenuItem::linkToCrud('Recettes', 'fa fa-utensils', Recipe::class);
        yield MenuItem::linkToCrud('Messages des visiteurs', 'fa fa-envelope', ContactMessage::class);
        yield MenuItem::linkToCrud('Avis des visiteurs', 'fa fa-comments', Review::class);
        yield MenuItem::linkToCrud('Administrateurs kocinaspeed', 'fa fa-user', User::class);
    }
}
