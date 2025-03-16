<?php

declare(strict_types=1);

namespace App\Tests\Unit\PullRequest;

use App\Entity\GitHubSlackMapping;
use App\Entity\SlackMessage;
use App\PullRequest\DraftPrUseCase;
use App\Repository\GitHubSlackMappingRepositoryInterface;
use App\Repository\SlackMessageRepositoryInterface;
use App\Slack\SlackApiClient;
use App\Slack\SlackResponse;
use App\Transfers\WebHookTransfer;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class DraftPrUseCaseTest extends TestCase
{
    private EntityManagerInterface $entityManager;

    private SlackMessageRepositoryInterface $slackMessageRepository;

    private GitHubSlackMappingRepositoryInterface $gitHubSlackMappingRepository;

    private SlackApiClient $slackApiClient;

    private DraftPrUseCase $useCase;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->slackMessageRepository = $this->createMock(SlackMessageRepositoryInterface::class);
        $this->gitHubSlackMappingRepository = $this->createMock(GitHubSlackMappingRepositoryInterface::class);
        $this->slackApiClient = $this->createMock(SlackApiClient::class);
        $this->useCase = new DraftPrUseCase(
            $this->entityManager,
            $this->slackMessageRepository,
            $this->gitHubSlackMappingRepository,
            $this->createMock(LoggerInterface::class),
            $this->slackApiClient,
        );
    }

    public function testHandleDraftFromScratch(): void
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
            ->method('postChatMessage');
        $this->entityManager->expects($this->never())
            ->method('persist');

        // Act
        $this->useCase->handle($webHookTransfer);
    }

    public function testHandleDraftNoSlackMapping(): void
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

        $this->gitHubSlackMappingRepository->expects($this->once())
            ->method('findByRepository')
            ->willReturn(null);

        $this->slackApiClient->expects($this->never())
            ->method('postChatMessage');
        $this->entityManager->expects($this->never())
            ->method('persist');

        // Act
        $this->useCase->handle($webHookTransfer);
    }

    public function testHandleDraftPrAfterReadyForReview(): void
    {
        // Arrange
        $webHookTransfer = new WebHookTransfer(
            repository: 'example/repo',
            prNumber: 42,
            prTitle: 'The title',
            prUrl: 'https://github.com/example/repo/pull/42',
            prAuthor: 'testuser'
        );

        $slackMessageEntity = new SlackMessage()->setPrNumber(42)->setTs('12345.6789');

        // Assert
        $this->slackMessageRepository->expects($this->once())
            ->method('findOneByPrNumberAndRepository')
            ->with(42, 'example/repo')
            ->willReturn($slackMessageEntity);

        $gitHubSlackMapping = new GitHubSlackMapping()
            ->setSlackChannel('test-slack-channel')
            ->setRepository('test-github-repository')
            ->setMentions(['<!subtram^S12345678>']);

        $this->gitHubSlackMappingRepository->expects($this->once())
            ->method('findByRepository')
            ->willReturn($gitHubSlackMapping);

        $this->slackApiClient->expects($this->once())
            ->method('removeSlackMessage')
            ->with($slackMessageEntity)
            ->willReturn(new SlackResponse());

        $this->entityManager->expects($this->once())
            ->method('remove')
            ->with($slackMessageEntity);

        // Act
        $this->useCase->handle($webHookTransfer);
    }
}
