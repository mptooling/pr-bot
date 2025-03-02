<?php

declare(strict_types=1);

namespace App\Command;

use App\Repository\GitHubSlackMappingRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'gsm:delete',
    description: <<<'EOF'
Remove a GitHub repository-to-Slack mapping from the database.
Use this command to delete a mapping when it's no longer needed.
EOF
)]
final class DeleteGithubSlackMappingCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly GitHubSlackMappingRepositoryInterface $repository
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument(
            'repository',
            InputArgument::REQUIRED,
            'GitHub repository name to delete (e.g., org/repo-backend)',
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $repository = $input->getArgument('repository');
        $mappingEntity = $this->repository->findByRepository($repository);

        if (!$mappingEntity) {
            $io->error("No mapping found for repository $repository.");

            return Command::FAILURE;
        }

        if (!$io->confirm("Are you sure you want to delete the mapping for repository '$repository'?", false)) {
            $io->warning("Deletion cancelled.");

            return Command::SUCCESS;
        }

        $this->entityManager->remove($mappingEntity);
        $this->entityManager->flush();

        $io->success("Successfully deleted mapping for repository $repository.");

        return Command::SUCCESS;
    }
}
