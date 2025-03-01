<?php

namespace App\Command;

use App\Entity\GitHubSlackMapping;
use App\Repository\GitHubSlackMappingRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'github-slack-mapping:write',
    description: <<<'EOF'
Add a new GitHub repository-to-slack mapping.
This configuration ensures that the application directs messages to the correct Slack channel based on 
the originating repository event.
If repository mapping already exists, it will be updated.
EOF
)]
class WriteGithubSlackMappingCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly GitHubSlackMappingRepositoryInterface $repository
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('repository', InputArgument::REQUIRED, 'GitHub repository name (e.g., org/repo-backend)')
            ->addArgument('slackChannel', InputArgument::REQUIRED, 'Slack channel name (e.g., #backend-alerts)')
            ->addArgument(
                'mentions',
                InputArgument::REQUIRED,
                'Comma-separated Slack group IDs (e.g., S12345678,S87654321)'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $repository = $input->getArgument('repository');
        $slackChannel = $input->getArgument('slackChannel');
        $mentions = explode(',', $input->getArgument('mentions') ?? '');

        $mappingEntity = $this->repository->findByRepository($repository);
        $mappingEntity = $mappingEntity ?? $this->createEntity($repository);

        if ($mappingEntity->getId() !== null) {
            $output->writeln("The repository $repository already exists. Updating configuration...");
        }

        $this->store($mappingEntity, $slackChannel, $mentions);

        $output->writeln("Data for repository $repository is stored.");

        return Command::SUCCESS;
    }

    private function createEntity(string $repository): GitHubSlackMapping
    {
        $mappingEntity = new GitHubSlackMapping();
        $mappingEntity->setRepository($repository);

        return $mappingEntity;
    }

    /**
     * @param list<string> $mentions
     */
    private function store(GitHubSlackMapping $mappingEntity, string $slackChannel, array $mentions): void
    {
        $mappingEntity->setSlackChannel($slackChannel);
        $mappingEntity->setMentions($mentions);

        $this->entityManager->persist($mappingEntity);
        $this->entityManager->flush();
    }
}
