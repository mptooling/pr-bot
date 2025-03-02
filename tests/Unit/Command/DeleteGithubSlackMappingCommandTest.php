<?php

declare(strict_types=1);

namespace App\Tests\Unit\Command;

use App\Command\DeleteGithubSlackMappingCommand;
use App\Entity\GitHubSlackMapping;
use App\Repository\GitHubSlackMappingRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class DeleteGithubSlackMappingCommandTest extends TestCase
{
    private EntityManagerInterface $entityManager;
    private GitHubSlackMappingRepository $repository;
    private CommandTester $commandTester;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->repository = $this->createMock(GitHubSlackMappingRepository::class);

        $command = new DeleteGithubSlackMappingCommand($this->entityManager, $this->repository);

        $application = new Application();
        $application->add($command);

        $this->commandTester = new CommandTester($application->find('github-slack-mapping:delete'));
    }

    public function testDeletesMappingSuccessfully(): void
    {
        $mapping = $this->createMock(GitHubSlackMapping::class);
        $this->repository->expects($this->once())
            ->method('findByRepository')
            ->with('org/repo-backend')
            ->willReturn($mapping);

        $this->entityManager->expects($this->once())
            ->method('remove')
            ->with($mapping);
        $this->entityManager->expects($this->once())
            ->method('flush');

        $this->commandTester->setInputs(['yes']);
        $this->commandTester->execute(['repository' => 'org/repo-backend']);

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Successfully deleted mapping for repository org/repo-backend.', $output);
    }

    public function testDisplaysErrorWhenMappingNotFound(): void
    {
        $this->repository->expects($this->once())
            ->method('findByRepository')
            ->with('org/repo-backend')
            ->willReturn(null);

        $this->commandTester->execute(['repository' => 'org/repo-backend']);

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('No mapping found for repository org/repo-backend.', $output);
    }

    public function testCancelsDeletionWhenNotConfirmed(): void
    {
        $mapping = $this->createMock(GitHubSlackMapping::class);
        $this->repository->expects($this->once())
            ->method('findByRepository')
            ->with('org/repo-backend')
            ->willReturn($mapping);

        $this->entityManager->expects($this->never())
            ->method('remove');
        $this->entityManager->expects($this->never())
            ->method('flush');

        $this->commandTester->setInputs(['no']);
        $this->commandTester->execute(['repository' => 'org/repo-backend']);

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Deletion cancelled.', $output);
    }
}
