<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class AuthTestController extends AbstractController
{
    #[Route('/auth-test', name: 'auth_test')]
    public function test(EntityManagerInterface $em, UserPasswordHasherInterface $hasher): Response
    {
        $user = $em->getRepository(User::class)->findOneBy(['email' => 'admin@kocinaspeed.fr']);
        
        if (!$user) {
            return new Response('Utilisateur non trouvé');
        }
        
        $passwordValid = $hasher->isPasswordValid($user, 'admin123');
        
        $debug = [
            'User found' => $user ? 'YES' : 'NO',
            'Email' => $user->getEmail(),
            'Roles' => $user->getRoles(),
            'Password valid' => $passwordValid ? 'YES' : 'NO',
            'Hash in DB' => $user->getPassword(),
            'PHP verification' => password_verify('admin123', $user->getPassword()) ? 'YES' : 'NO'
        ];
        
        return new Response('<pre>' . print_r($debug, true) . '</pre>');
    }
}