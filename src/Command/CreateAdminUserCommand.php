<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-admin-user',
    description: 'Crée un utilisateur administrateur.',
)]
class CreateAdminUserCommand extends Command
{
    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher)
    {
        parent::__construct();

        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Créer un nouvel utilisateur
        $user = new User();
        $user->setEmail('c.mostefaoui@yahoo.fr');
        $user->setName('Administrateur');

        // Définir le rôle administrateur
        $user->setRoles(['ROLE_ADMIN']);

        // Définir le mot de passe
        $plainPassword = 'Mostefaoui1';
        $hashedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);
        $user->setPassword($hashedPassword);

        // Enregistrer l'utilisateur en base de données
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $output->writeln('Utilisateur administrateur créé avec succès !');

        return Command::SUCCESS;
    }
}