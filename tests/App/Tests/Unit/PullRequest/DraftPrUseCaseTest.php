<?php

declare(strict_types=1);

namespace App\Tests\App\Tests\Unit\PullRequest;

use App\Entity\SlackMessage;
use App\PullRequest\DraftPrUseCase;
use App\Repository\SlackMessageRepositoryInterface;
use App\Slack\SlackMessengerInterface;
use App\Transfers\WebHookTransfer;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class DraftPrUseCaseTest extends TestCase
{
    private EntityManagerInterface $entityManager;

    private SlackMessageRepositoryInterface $slackMessageRepository;

    private SlackMessengerInterface $slackMessenger;

    private DraftPrUseCase $useCase;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->slackMessageRepository = $this->createMock(SlackMessageRepositoryInterface::class);
        $this->slackMessenger = $this->createMock(SlackMessengerInterface::class);
        $this->useCase = new DraftPrUseCase(
            $this->entityManager,
            $this->slackMessageRepository,
            $this->slackMessenger,
            $this->createMock(LoggerInterface::class)
        );
    }
    public function testHandleDraftFromScratch(): void
    {
        // Arrange
        $webHookTransfer = new WebHookTransfer(
            42,
            'https://github.com/example/repo/pull/42',
            'testuser'
        );

        // Assert
        $this->slackMessageRepository->expects($this->once())
            ->method('findOneByPrNumber')
            ->with(42)
            ->willReturn(null);

        $this->slackMessenger->expects($this->never())
            ->method('sendNewMessage');
        $this->entityManager->expects($this->never())
            ->method('persist');

        // Act
        $this->useCase->handle($webHookTransfer);
    }

    public function testHandleDraftPrAfterReadyForReview(): void
    {
        // Arrange
        $webHookTransfer = new WebHookTransfer(
            42,
            'https://github.com/example/repo/pull/42',
            'testuser'
        );

        $slackMessageEntity = new SlackMessage()->setPrNumber(42)->setTs('12345.6789');

        // Assert
        $this->slackMessageRepository->expects($this->once())
            ->method('findOneByPrNumber')
            ->with(42)
            ->willReturn($slackMessageEntity);

        $this->slackMessenger->expects($this->once())
            ->method('removeMessage')
            ->with($slackMessageEntity)
            ->willReturn(true);

        $this->entityManager->expects($this->once())
            ->method('remove')
            ->with($slackMessageEntity);

        // Act
        $this->useCase->handle($webHookTransfer);
    }
}
