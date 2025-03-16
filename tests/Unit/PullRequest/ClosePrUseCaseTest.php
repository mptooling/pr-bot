<?php

declare(strict_types=1);

namespace App\Tests\Unit\PullRequest;

use App\Entity\GitHubSlackMapping;
use App\Entity\SlackMessage;
use App\PullRequest\ClosePrUseCase;
use App\Repository\GitHubSlackMappingRepositoryInterface;
use App\Repository\SlackMessageRepositoryInterface;
use App\Slack\SlackApiClient;
use App\Slack\SlackMessageComposer;
use App\Slack\SlackResponse;
use App\Transfers\WebHookTransfer;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class ClosePrUseCaseTest extends TestCase
{
    private SlackMessageRepositoryInterface $slackMessageRepository;

    private GitHubSlackMappingRepositoryInterface $gitHubSlackMappingRepository;

    private SlackApiClient $slackApiClient;

    private SlackMessageComposer $slackMessageComposer;

    private ClosePrUseCase $useCase;

    protected function setUp(): void
    {
        $this->slackMessageRepository = $this->createMock(SlackMessageRepositoryInterface::class);
        $this->slackApiClient = $this->createMock(SlackApiClient::class);
        $this->slackMessageComposer = $this->createMock(SlackMessageComposer::class);
        $this->gitHubSlackMappingRepository = $this->createMock(GitHubSlackMappingRepositoryInterface::class);
        $this->useCase = new ClosePrUseCase(
            $this->slackMessageRepository,
            $this->gitHubSlackMappingRepository,
            $this->slackMessageComposer,
            $this->slackApiClient,
            $this->createMock(LoggerInterface::class),
            true,
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

        $this->slackApiClient->expects($this->never())
            ->method('updateChatMessage');

        $this->slackApiClient->expects($this->never())
            ->method('addReaction');


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

        $this->slackApiClient->expects($this->never())
            ->method('updateChatMessage');

        $this->slackApiClient->expects($this->never())
            ->method('addReaction');

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
            prAuthor: 'testuser',
            isMerged: true
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

        $this->slackApiClient->expects($this->once())
            ->method('updateChatMessage')
            ->willReturn(new SlackResponse('1234567890.123456'));

        $this->slackApiClient->expects($this->once())
            ->method('addReaction')
            ->willReturn(new SlackResponse());

        // Act
        $this->useCase->handle($webHookTransfer);
    }
}
