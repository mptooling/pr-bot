<?php

declare(strict_types=1);

namespace App\Tests\Unit\Command;

use App\Command\ListGithubSlackMappingsCommand;
use App\Entity\GitHubSlackMapping;
use App\Repository\GitHubSlackMappingRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class ListGithubSlackMappingCommandTest extends TestCase
{
    private GitHubSlackMappingRepository $repository;
    private CommandTester $commandTester;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(GitHubSlackMappingRepository::class);

        $command = new ListGithubSlackMappingsCommand($this->repository);

        $application = new Application();
        $application->add($command);

        $this->commandTester = new CommandTester($application->find('gsm:list'));
    }

    public function testDisplaysNoMappingsFoundWhenRepositoryIsEmpty(): void
    {
        $this->repository->expects($this->once())
            ->method('findAll')
            ->willReturn([]);

        $this->commandTester->execute([]);

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('No GitHub-to-Slack mappings found.', $output);
    }

    public function testDisplaysMappingsCorrectly(): void
    {
        $mapping = $this->createMock(GitHubSlackMapping::class);
        $mapping->method('getRepository')->willReturn('org/repo-backend');
        $mapping->method('getSlackChannel')->willReturn('#backend-alerts');
        $mapping->method('getMentions')->willReturn(['S12345678', 'S87654321']);

        $this->repository->expects($this->once())
            ->method('findAll')
            ->willReturn([$mapping]);

        $this->commandTester->execute([]);

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('org/repo-backend', $output);
        $this->assertStringContainsString('#backend-alerts', $output);
        $this->assertStringContainsString('S12345678, S87654321', $output);
    }
}
