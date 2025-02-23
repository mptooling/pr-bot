<?php

declare(strict_types=1);

namespace App\PullRequest;

use App\Entity\SlackMessage;
use App\Repository\SlackMessageRepositoryInterface;
use App\Slack\SlackMessengerInterface;
use App\Transfers\WebHookTransfer;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

final readonly class ClosePrUseCase
{
    public function __construct(
        private SlackMessageRepositoryInterface $slackMessageRepository,
        private SlackMessengerInterface $slackMessenger,
        private LoggerInterface $logger,
    ) {
    }

    public function handle(WebHookTransfer $webHookTransfer): void
    {
        $slackMessage = $this->slackMessageRepository->findOneByPrNumber($webHookTransfer->prNumber);
        if (!$slackMessage) {
            $this->logger->error('No message found', ['prNumber' => $webHookTransfer->prNumber]);

            return;
        }

        $slackResponse = $this->slackMessenger->updateMessage($webHookTransfer, $slackMessage);
        if (!isset($slackResponse['ts'])) {
            $this->logger->error('Slack message not sent', [
                'prNumber' => $webHookTransfer->prNumber,
                'response' => $slackResponse,
            ]);

            return;
        }

        $this->logger->info('Slack message updated', ['prNumber' => $webHookTransfer->prNumber]);
    }
}
