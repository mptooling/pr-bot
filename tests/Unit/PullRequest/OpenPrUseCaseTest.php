<?php

declare(strict_types=1);

namespace App\Tests\Unit\PullRequest;

use App\Entity\GitHubSlackMapping;
use App\Entity\SlackMessage;
use App\PullRequest\OpenPrUseCase;
use App\Repository\GitHubSlackMappingRepositoryInterface;
use App\Repository\SlackMessageRepositoryInterface;
use App\Slack\SlackApiClient;
use App\Slack\SlackMessageComposer;
use App\Slack\SlackResponse;
use App\Transfers\WebHookTransfer;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class OpenPrUseCaseTest extends TestCase
{
    private SlackMessageRepositoryInterface $slackMessageRepository;

    private GitHubSlackMappingRepositoryInterface $gitHubSlackMappingRepository;

    private SlackMessageComposer $slackMessageComposer;

    private SlackApiClient $slackMessenger;

    private OpenPrUseCase $useCase;

    protected function setUp(): void
    {
        $this->slackMessageRepository = $this->createMock(SlackMessageRepositoryInterface::class);
        $this->gitHubSlackMappingRepository = $this->createMock(GitHubSlackMappingRepositoryInterface::class);
        $this->slackMessageComposer = $this->createMock(SlackMessageComposer::class);
        $this->slackMessenger = $this->createMock(SlackApiClient::class);
        $this->useCase = new OpenPrUseCase(
            $this->slackMessageRepository,
            $this->gitHubSlackMappingRepository,
            $this->createMock(LoggerInterface::class),
            $this->slackMessageComposer,
            $this->slackMessenger,
        );
    }

    public function testHandleDoesNothingIfDuplicate(): void
    {
        // Arrange & Assert
        $webHookTransfer = new WebHookTransfer(
            repository: 'example/repo',
            prNumber: 42,
            prTitle: 'The title',
            prUrl: 'https://github.com/example/rzepo/pull/42',
            prAuthor: 'testuser'
        );

        $this->slackMessageRepository->expects($this->once())
            ->method('findOneByPrNumberAndRepository')
            ->with(42, 'example/repo')
            ->willReturn(new SlackMessage(
                prNumber: 42,
                ghRepository: 'example/repo',
                ts: '1234567890.123456',
            ));

        $this->gitHubSlackMappingRepository->expects($this->never()) ->method('findByRepository');

        $this->slackMessageComposer->expects($this->never())
            ->method('composeNewSlackMessage');

        $this->slackMessenger->expects($this->never())
            ->method('postChatMessage');

        $this->slackMessageRepository->expects($this->never())
            ->method('saveSlackMessage');

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
            ->willReturn(null);

        $this->gitHubSlackMappingRepository->expects($this->once())
            ->method('findByRepository')
            ->willReturn(null);

        $this->slackMessageComposer->expects($this->never())
            ->method('composeNewSlackMessage');

        $this->slackMessenger->expects($this->never())
            ->method('postChatMessage');

        $this->slackMessageRepository->expects($this->never())
            ->method('saveSlackMessage');

        // Act
        $this->useCase->handle($webHookTransfer);
    }

    public function testHandleSendsSlackMessageButFails(): void
    {
        // Arrange & Assert
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

        $gitHubSlackMapping = new GitHubSlackMapping()
            ->setSlackChannel('test-slack-channel')
            ->setRepository('test-github-repository')
            ->setMentions(['<!subtram^S12345678>']);

        $this->gitHubSlackMappingRepository->expects($this->once())
            ->method('findByRepository')
            ->willReturn($gitHubSlackMapping);

        $this->slackMessageComposer->expects($this->once())
            ->method('composeNewSlackMessage');

        $this->slackMessenger->expects($this->once())
            ->method('postChatMessage')
            ->willReturn(SlackResponse::fail());

        $this->slackMessageRepository->expects($this->never())
            ->method('saveSlackMessage');

        // Act
        $this->useCase->handle($webHookTransfer);
    }


    public function testHandleSendsSlackMessageAndStoresSlackMessageIntoDb(): void
    {
        // Arrange & Assert
        $webHookTransfer = new WebHookTransfer(
            repository: 'example/repo',
            prNumber: 42,
            prTitle: 'The title',
            prUrl: 'https://github.com/example/repo/pull/42',
            prAuthor: 'testuser'
        );

        $gitHubSlackMapping = new GitHubSlackMapping()
            ->setSlackChannel('test-slack-channel')
            ->setRepository('test-github-repository')
            ->setMentions(['<!subtram^S12345678>']);

        $this->gitHubSlackMappingRepository->expects($this->once())
            ->method('findByRepository')
            ->willReturn($gitHubSlackMapping);

        $this->slackMessageComposer->expects($this->once())
            ->method('composeNewSlackMessage');

        $this->slackMessenger->expects($this->once())
            ->method('postChatMessage')
            ->willReturn(new SlackResponse(slackMessageId: '1234567890'));

        $this->slackMessageRepository->expects($this->once())
            ->method('saveSlackMessage');

        // Act
        $this->useCase->handle($webHookTransfer);
    }
}
