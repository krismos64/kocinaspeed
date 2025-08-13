<?php

namespace App\Command;

use App\Service\ImageOptimizerService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\Finder;

#[AsCommand(
    name: 'app:optimize-images',
    description: 'Optimise toutes les images du projet'
)]
class OptimizeImagesCommand extends Command
{
    private ImageOptimizerService $imageOptimizer;
    private string $projectDir;

    public function __construct(ImageOptimizerService $imageOptimizer, string $projectDir)
    {
        parent::__construct();
        $this->imageOptimizer = $imageOptimizer;
        $this->projectDir = $projectDir;
    }

    protected function configure(): void
    {
        $this
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Afficher les optimisations sans les appliquer')
            ->addOption('directory', 'd', InputOption::VALUE_REQUIRED, 'Répertoire spécifique à optimiser')
            ->setHelp('Cette commande optimise toutes les images JPEG/PNG pour réduire leur taille.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $dryRun = $input->getOption('dry-run');
        $specificDir = $input->getOption('directory');

        $io->title('🖼️ Optimisation des images KocinaSpeed');

        if ($dryRun) {
            $io->note('Mode DRY-RUN : Aucune modification ne sera effectuée');
        }

        // Définir les répertoires à scanner
        $directories = $specificDir ? 
            [$this->projectDir . '/public/' . $specificDir] : 
            [
                $this->projectDir . '/public/img',
                $this->projectDir . '/public/uploads'
            ];

        $totalSavings = 0;
        $totalFiles = 0;
        $optimizedCount = 0;

        foreach ($directories as $directory) {
            if (!is_dir($directory)) {
                $io->warning("Répertoire inexistant : $directory");
                continue;
            }

            $io->section("📁 Scan du répertoire : " . basename($directory));

            $finder = new Finder();
            $finder->files()
                   ->in($directory)
                   ->name('/\.(jpe?g|png|gif)$/i')
                   ->size('> 50K'); // Seulement les images > 50KB

            if (!$finder->hasResults()) {
                $io->text('Aucune image à optimiser trouvée');
                continue;
            }

            $progressBar = $io->createProgressBar(iterator_count($finder));
            $progressBar->start();

            foreach ($finder as $file) {
                $totalFiles++;
                $filePath = $file->getRealPath();
                $originalSize = $file->getSize();

                if (!$dryRun) {
                    $success = $this->imageOptimizer->optimizeExistingFile($filePath);
                    
                    if ($success) {
                        $newSize = filesize($filePath);
                        $savings = $originalSize - $newSize;
                        $totalSavings += $savings;
                        $optimizedCount++;

                        if ($savings > 0) {
                            $percentage = ($savings / $originalSize) * 100;
                            $io->text(sprintf(
                                '✅ %s: %s → %s (-%s, -%.1f%%)',
                                basename($filePath),
                                $this->formatBytes($originalSize),
                                $this->formatBytes($newSize),
                                $this->formatBytes($savings),
                                $percentage
                            ));
                        }
                    }
                } else {
                    // Mode dry-run : simuler l'optimisation
                    $estimatedSavings = $originalSize * 0.3; // Estimation 30% de réduction
                    $totalSavings += $estimatedSavings;
                    $optimizedCount++;

                    $io->text(sprintf(
                        '🔍 %s: %s (économie estimée: %s)',
                        basename($filePath),
                        $this->formatBytes($originalSize),
                        $this->formatBytes($estimatedSavings)
                    ));
                }

                $progressBar->advance();
            }

            $progressBar->finish();
            $io->newLine(2);
        }

        // Résumé
        $io->success('🎉 Optimisation terminée !');
        
        $io->table(
            ['Statistique', 'Valeur'],
            [
                ['Images traitées', $totalFiles],
                ['Images optimisées', $optimizedCount],
                ['Espace économisé', $this->formatBytes($totalSavings)],
                ['Mode', $dryRun ? 'DRY-RUN (simulation)' : 'RÉEL']
            ]
        );

        if ($dryRun && $totalSavings > 0) {
            $io->note('Pour appliquer les optimisations, relancez sans --dry-run');
        }

        return Command::SUCCESS;
    }

    private function formatBytes(int $size): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $factor = floor((strlen($size) - 1) / 3);
        return sprintf("%.1f %s", $size / pow(1024, $factor), $units[$factor]);
    }
}