<?php

declare(strict_types=1);

namespace App\PullRequest;

use App\Repository\GitHubSlackMappingRepositoryInterface;
use App\Repository\SlackMessageRepositoryInterface;
use App\Slack\SlackApiClient;
use App\Slack\SlackMessageComposer;
use App\Transfers\WebHookTransfer;
use Psr\Log\LoggerInterface;

final readonly class ClosePrUseCase implements PrEventHandlerInterface
{
    public function __construct(
        private SlackMessageRepositoryInterface $slackMessageRepository,
        private GitHubSlackMappingRepositoryInterface $gitHubSlackMappingRepository,
        private SlackMessageComposer $slackMessegeComposer,
        private SlackApiClient $slackApiClient,
        private LoggerInterface $logger,
        private bool $isReactionsEnabled = false,
        private string $slackReactionMergedPr = 'white_check_mark',
        private string $slackReactionClosedPr = 'no_entry_sign',
    ) {
    }

    public function handle(WebHookTransfer $webHookTransfer): void
    {
        $slackMessage = $this->slackMessageRepository->findOneByPrNumberAndRepository(
            $webHookTransfer->prNumber,
            $webHookTransfer->repository,
        );
        if (!$slackMessage) {
            $this->logger->error('No message found', ['prNumber' => $webHookTransfer->prNumber]);

            return;
        }

        $slackMapping = $this->gitHubSlackMappingRepository->findByRepository($webHookTransfer->repository);
        if ($slackMapping === null) {
            $this->logger->error('Slack mapping not found', ['repository' => $webHookTransfer->repository]);

            return;
        }

        $message = $this->slackMessegeComposer->composeUpdatedMessage($webHookTransfer, $slackMapping);
        $slackResponse = $this->slackApiClient->updateChatMessage(
            $slackMapping,
            $message,
            (string) $slackMessage->getTs()
        );

        if (!$slackResponse->isSuccessful) {
            $this->logger->error('Slack message not sent', [
                'prNumber' => $webHookTransfer->prNumber,
                'response' => $slackResponse,
            ]);

            return;
        }

        if ($this->isReactionsEnabled) {
            $reaction = $webHookTransfer->isMerged ? $this->slackReactionMergedPr : $this->slackReactionClosedPr;
            $this->slackApiClient->addReaction($slackMapping, (string) $slackMessage->getTs(), $reaction);
        }

        $this->logger->debug('Slack message updated', ['prNumber' => $webHookTransfer->prNumber]);
    }

    public function isApplicable(string $action): bool
    {
        return $action === 'closed';
    }
}
