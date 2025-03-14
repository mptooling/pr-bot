<?php

declare(strict_types=1);

namespace App\Tests\Unit\PullRequest;

use App\Entity\GitHubSlackMapping;
use App\Entity\SlackMessage;
use App\PullRequest\ClosePrUseCase;
use App\Repository\GitHubSlackMappingRepositoryInterface;
use App\Repository\SlackMessageRepositoryInterface;
use App\Slack\SlackMessengerInterface;
use App\Transfers\WebHookTransfer;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class ClosePrUseCaseTest extends TestCase
{
    private SlackMessageRepositoryInterface $slackMessageRepository;

    private GitHubSlackMappingRepositoryInterface $gitHubSlackMappingRepository;

    private SlackMessengerInterface $slackMessenger;

    private ClosePrUseCase $useCase;

    protected function setUp(): void
    {
        $this->slackMessageRepository = $this->createMock(SlackMessageRepositoryInterface::class);
        $this->slackMessenger = $this->createMock(SlackMessengerInterface::class);
        $this->gitHubSlackMappingRepository = $this->createMock(GitHubSlackMappingRepositoryInterface::class);
        $this->useCase = new ClosePrUseCase(
            $this->slackMessageRepository,
            $this->gitHubSlackMappingRepository,
            $this->slackMessenger,
            $this->createMock(LoggerInterface::class)
        );
    }

    public function testHandleDoesNothingIfNoMessageStoredForPrNumber(): void
    {
        // Arrange
        $webHookTransfer = new WebHookTransfer(
            repository: 'example/repo',
            prNumber: 42,
            prTitle: 'The title',
            prUrl: 'https://github.com/example/repo/pull/42',
            prAuthor: 'testuser'
        );

        // Assert
        $this->slackMessageRepository->expects($this->once())
            ->method('findOneByPrNumberAndRepository')
            ->with(42, 'example/repo')
            ->willReturn(null);

        $this->gitHubSlackMappingRepository->expects($this->never())->method('findByRepository');

        $this->slackMessenger->expects($this->never())
            ->method('updateMessage');

        // Act
        $this->useCase->handle($webHookTransfer);
    }

    public function testHandleDoesNothingIfNoSlackMappingFound(): void
    {
        // Arrange & Assert
        $webHookTransfer = new WebHookTransfer(
            repository: 'example/repo',
            prNumber: 42,
            prTitle: 'The title',
            prUrl: 'https://github.com/example/repo/pull/42',
            prAuthor: 'testuser'
        );

        $this->slackMessageRepository->expects($this->once())
            ->method('findOneByPrNumberAndRepository')
            ->with(42, 'example/repo')
            ->willReturn(new SlackMessage());

        $this->gitHubSlackMappingRepository->expects($this->once())
            ->method('findByRepository')
            ->willReturn(null);

        $this->slackMessenger->expects($this->never())
            ->method('updateMessage');

        // Act
        $this->useCase->handle($webHookTransfer);
    }

    public function testHandleSendsMessage(): void
    {
        // Arrange
        $webHookTransfer = new WebHookTransfer(
            repository: 'example/repo',
            prNumber: 42,
            prTitle: 'The title',
            prUrl: 'https://github.com/example/repo/pull/42',
            prAuthor: 'testuser'
        );

        // Assert
        $this->slackMessageRepository->expects($this->once())
            ->method('findOneByPrNumberAndRepository')
            ->with(42, 'example/repo')
            ->willReturn(new SlackMessage());

        $gitHubSlackMapping = new GitHubSlackMapping()
            ->setSlackChannel('test-slack-channel')
            ->setRepository('test-github-repository')
            ->setMentions(['<!subtram^S12345678>']);

        $this->gitHubSlackMappingRepository->expects($this->once())
            ->method('findByRepository')
            ->willReturn($gitHubSlackMapping);

        $this->slackMessenger->expects($this->once())
            ->method('updateMessage');

        // Act
        $this->useCase->handle($webHookTransfer);
    }
}
