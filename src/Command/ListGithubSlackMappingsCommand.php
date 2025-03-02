<?php

declare(strict_types=1);

namespace App\Command;

use App\Repository\GitHubSlackMappingRepositoryInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'github-slack-mapping:list',
    description: <<<'EOF'
Display all GitHub repository-to-Slack mappings.
This command provides an overview of configured mappings, including repositories, Slack channels, and mentions.
EOF
)]
class ListGithubSlackMappingsCommand extends Command
{
    public function __construct(
        private readonly GitHubSlackMappingRepositoryInterface $repository
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $mappings = $this->repository->findAll();

        if ($mappings === []) {
            $output->writeln('<comment>No GitHub-to-Slack mappings found.</comment>');

            return Command::SUCCESS;
        }

        $table = new Table($output);
        $table
            ->setHeaders(['Repository', 'Slack Channel', 'Mentions'])
            ->setRows(
                array_map(fn($mapping) => [
                    $mapping->getRepository(),
                    $mapping->getSlackChannel(),
                    implode(', ', $mapping->getMentions()),
                ], $mappings)
            );

        $table->render();

        return Command::SUCCESS;
    }
}
