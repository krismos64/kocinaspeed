<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
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
        $io = new SymfonyStyle($input, $output);
        $helper = $this->getHelper('question');

        $io->title('🔐 Création d\'un utilisateur administrateur');

        // Demander l'email de manière interactive
        $emailQuestion = new Question('📧 Email de l\'administrateur: ');
        $emailQuestion->setValidator(function ($value) {
            if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                throw new \RuntimeException('Veuillez entrer un email valide.');
            }
            return $value;
        });
        $email = $helper->ask($input, $output, $emailQuestion);

        // Vérifier si l'utilisateur existe déjà
        $existingUser = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
        if ($existingUser) {
            $io->error('Un utilisateur avec cet email existe déjà !');
            return Command::FAILURE;
        }

        // Demander le nom
        $nameQuestion = new Question('👤 Nom de l\'administrateur: ', 'Administrateur');
        $name = $helper->ask($input, $output, $nameQuestion);

        // Demander le mot de passe de manière sécurisée
        $passwordQuestion = new Question('🔑 Mot de passe (min 8 caractères): ');
        $passwordQuestion->setHidden(true);
        $passwordQuestion->setHiddenFallback(false);
        $passwordQuestion->setValidator(function ($value) {
            if (strlen($value) < 8) {
                throw new \RuntimeException('Le mot de passe doit contenir au moins 8 caractères.');
            }
            return $value;
        });
        $plainPassword = $helper->ask($input, $output, $passwordQuestion);

        // Confirmer le mot de passe
        $confirmPasswordQuestion = new Question('🔑 Confirmer le mot de passe: ');
        $confirmPasswordQuestion->setHidden(true);
        $confirmPasswordQuestion->setHiddenFallback(false);
        $confirmPassword = $helper->ask($input, $output, $confirmPasswordQuestion);

        if ($plainPassword !== $confirmPassword) {
            $io->error('Les mots de passe ne correspondent pas !');
            return Command::FAILURE;
        }

        // Créer l'utilisateur
        $user = new User();
        $user->setEmail($email);
        $user->setName($name);
        $user->setRoles(['ROLE_ADMIN']);

        // Hasher le mot de passe
        $hashedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);
        $user->setPassword($hashedPassword);

        // Enregistrer en base
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $io->success('✅ Utilisateur administrateur créé avec succès !');
        $io->table(['Email', 'Nom', 'Rôles'], [[$email, $name, 'ROLE_ADMIN']]);

        return Command::SUCCESS;
    }
}
