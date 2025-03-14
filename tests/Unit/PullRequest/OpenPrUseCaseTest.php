<?php

declare(strict_types=1);

namespace App\Tests\Unit\PullRequest;

use App\Entity\GitHubSlackMapping;
use App\Entity\SlackMessage;
use App\PullRequest\OpenPrUseCase;
use App\Repository\GitHubSlackMappingRepositoryInterface;
use App\Repository\SlackMessageRepositoryInterface;
use App\Slack\SlackMessengerInterface;
use App\Transfers\WebHookTransfer;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class OpenPrUseCaseTest extends TestCase
{
    private EntityManagerInterface $entityManager;

    private SlackMessageRepositoryInterface $slackMessageRepository;

    private GitHubSlackMappingRepositoryInterface $gitHubSlackMappingRepository;

    private SlackMessengerInterface $slackMessenger;

    private OpenPrUseCase $useCase;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->slackMessageRepository = $this->createMock(SlackMessageRepositoryInterface::class);
        $this->gitHubSlackMappingRepository = $this->createMock(GitHubSlackMappingRepositoryInterface::class);
        $this->slackMessenger = $this->createMock(SlackMessengerInterface::class);
        $this->useCase = new OpenPrUseCase(
            $this->entityManager,
            $this->slackMessageRepository,
            $this->gitHubSlackMappingRepository,
            $this->slackMessenger,
            $this->createMock(LoggerInterface::class)
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
            ->willReturn(new SlackMessage());

        $this->gitHubSlackMappingRepository->expects($this->never()) ->method('findByRepository');

        $this->slackMessenger->expects($this->never())
            ->method('sendNewMessage');

        $this->entityManager->expects($this->never())
            ->method('persist');

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

        $this->slackMessenger->expects($this->never())
            ->method('sendNewMessage');

        $this->entityManager->expects($this->never())
            ->method('persist');

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

        $this->slackMessenger->expects($this->once())
            ->method('sendNewMessage')
            ->willReturn([]);

        $this->entityManager->expects($this->never())
            ->method('persist');

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

        $this->slackMessenger->expects($this->once())
            ->method('sendNewMessage')
            ->willReturn(['ts' => '1234567890']);

        $this->entityManager->expects($this->once())
            ->method('persist');

        $this->entityManager->expects($this->once())
            ->method('flush');

        // Act
        $this->useCase->handle($webHookTransfer);
    }
}
