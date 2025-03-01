<?php

declare(strict_types=1);

namespace App\Tests\App\Tests\Unit\Command;

use App\Command\WriteGithubSlackMappingCommand;
use App\Entity\GitHubSlackMapping;
use App\Repository\GitHubSlackMappingRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class WriteGithubSlackMappingCommandTest extends TestCase
{
    private EntityManagerInterface $entityManager;
    private GitHubSlackMappingRepositoryInterface $repository;
    private CommandTester $commandTester;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->repository = $this->createMock(GitHubSlackMappingRepositoryInterface::class);

        $command = new WriteGithubSlackMappingCommand($this->entityManager, $this->repository);

        $application = new Application();
        $application->add($command);

        $this->commandTester = new CommandTester($application->find('github-slack-mapping:write'));
    }

    public function testExecuteWithNewMapping(): void
    {
        $repositoryName = 'org/repo-backend';
        $slackChannel = '#backend-alerts';
        $mentions = 'S12345678,S87654321';

        $this->repository->expects($this->once())
            ->method('findByRepository')
            ->with($repositoryName)
            ->willReturn(null);

        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf(GitHubSlackMapping::class));

        $this->entityManager->expects($this->once())
            ->method('flush');

        $this->commandTester->execute([
            'repository'   => $repositoryName,
            'slackChannel' => $slackChannel,
            'mentions'     => $mentions,
        ]);

        $output = $this->commandTester->getDisplay();
        $this->assertStringNotContainsString(
            'The repository org/repo-backend already exists. Updating configuration...',
            $output
        );
    }

    public function testExecuteWithExistingMapping(): void
    {
        $repositoryName = 'org/repo-backend';
        $slackChannel = '#backend-alerts';
        $mentions = 'S12345678,S87654321';

        $existingMapping = $this->createMock(GitHubSlackMapping::class);
        $existingMapping->method('getId')->willReturn(1);

        $this->repository->expects($this->once())
            ->method('findByRepository')
            ->with($repositoryName)
            ->willReturn($existingMapping);

        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($existingMapping);

        $this->entityManager->expects($this->once())
            ->method('flush');

        $this->commandTester->execute([
            'repository'   => $repositoryName,
            'slackChannel' => $slackChannel,
            'mentions'     => $mentions,
        ]);

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString(
            'The repository org/repo-backend already exists. Updating configuration...',
            $output,
        );
    }
}
