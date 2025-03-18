<?php

declare(strict_types=1);

namespace App\PullRequest;

use App\Repository\GitHubSlackMappingRepositoryInterface;
use App\Repository\SlackMessageRepositoryInterface;
use App\Slack\SlackApiClient;
use App\Slack\SlackMessageComposer;
use App\Transfers\WebHookTransfer;
use Psr\Log\LoggerInterface;

final readonly class OpenPrUseCase implements PrEventHandlerInterface
{
    public function __construct(
        private SlackMessageRepositoryInterface $slackMessageRepository,
        private GitHubSlackMappingRepositoryInterface $gitHubSlackMappingRepository,
        private LoggerInterface $logger,
        private SlackMessageComposer $slackMessageComposer,
        private SlackApiClient $slackApiClient,
    ) {
    }

    public function handle(WebHookTransfer $webHookTransfer): void
    {
        $slackMessage = $this->slackMessageRepository->findOneByPrNumberAndRepository(
            $webHookTransfer->prNumber,
            $webHookTransfer->repository,
        );
        if ($slackMessage !== null) {
            $this->logger->info('Message already sent', ['prNumber' => $webHookTransfer->prNumber]);

            return;
        }

        $slackMapping = $this->gitHubSlackMappingRepository->findByRepository($webHookTransfer->repository);
        if ($slackMapping === null) {
            $this->logger->error('Slack mapping not found', ['repository' => $webHookTransfer->repository]);

            return;
        }

        $message = $this->slackMessageComposer->composeNewSlackMessage($webHookTransfer, $slackMapping);
        $slackResponse = $this->slackApiClient->postChatMessage($message, $slackMapping);
        if (!$slackResponse->isSuccessful) {
            $this->logger->error('Slack message not sent', [
                'prNumber' => $webHookTransfer->prNumber,
                'response' => $slackResponse,
            ]);

            return;
        }

        $this->slackMessageRepository->saveSlackMessage(
            $webHookTransfer->prNumber,
            $webHookTransfer->repository,
            (string) $slackResponse->slackMessageId,
        );

        $this->logger->info('Slack message sent', ['prNumber' => $webHookTransfer->prNumber]);
    }

    public function isApplicable(string $action, array $options = []): bool
    {
        return $action === 'opened' || $action === 'ready_for_review';
    }
}
