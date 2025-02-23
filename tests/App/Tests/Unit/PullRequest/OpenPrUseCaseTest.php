<?php

declare(strict_types=1);

namespace App\Tests\Unit\PullRequest;

use App\Entity\SlackMessage;
use App\PullRequest\OpenPrUseCase;
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

    private SlackMessengerInterface $slackMessenger;

    private OpenPrUseCase $useCase;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->slackMessageRepository = $this->createMock(SlackMessageRepositoryInterface::class);
        $this->slackMessenger = $this->createMock(SlackMessengerInterface::class);
        $this->useCase = new OpenPrUseCase(
            $this->entityManager,
            $this->slackMessageRepository,
            $this->slackMessenger,
            $this->createMock(LoggerInterface::class)
        );
    }

    public function testHandleDoesNothingIfDuplicate(): void
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
            ->willReturn(new SlackMessage());

        $this->slackMessenger->expects($this->never())
            ->method('sendNewMessage');

        $this->entityManager->expects($this->never())
            ->method('persist');

        // Act
        $this->useCase->handle($webHookTransfer);
    }

    public function testHandleSendsSlackMessageButFails(): void
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
