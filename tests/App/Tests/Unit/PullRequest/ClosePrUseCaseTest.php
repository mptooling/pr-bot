<?php

declare(strict_types=1);

namespace App\Tests\App\Tests\Unit\PullRequest;

use App\Entity\SlackMessage;
use App\PullRequest\ClosePrUseCase;
use App\PullRequest\OpenPrUseCase;
use App\Repository\SlackMessageRepositoryInterface;
use App\Slack\SlackMessengerInterface;
use App\Transfers\WebHookTransfer;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class ClosePrUseCaseTest extends TestCase
{
    private SlackMessageRepositoryInterface $slackMessageRepository;

    private SlackMessengerInterface $slackMessenger;

    private ClosePrUseCase $useCase;

    protected function setUp(): void
    {
        $this->slackMessageRepository = $this->createMock(SlackMessageRepositoryInterface::class);
        $this->slackMessenger = $this->createMock(SlackMessengerInterface::class);
        $this->useCase = new ClosePrUseCase(
            $this->slackMessageRepository,
            $this->slackMessenger,
            $this->createMock(LoggerInterface::class)
        );
    }

    public function testHandleDoesNothingIfNoMessageStoredForPrNumber(): void
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
            ->method('updateMessage');

        // Act
        $this->useCase->handle($webHookTransfer);
    }


    public function testHandleSendsMessage(): void
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

        $this->slackMessenger->expects($this->once())
            ->method('updateMessage');


        // Act
        $this->useCase->handle($webHookTransfer);
    }
}
