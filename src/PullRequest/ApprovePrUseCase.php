<?php

declare(strict_types=1);

namespace App\PullRequest;

use App\Entity\GitHubSlackMapping;
use App\Repository\GitHubSlackMappingRepositoryInterface;
use App\Repository\SlackMessageRepositoryInterface;
use App\Slack\SlackApiClient;
use App\Transfers\WebHookTransfer;
use Psr\Log\LoggerInterface;

/**
 * GH Event required data part:
 * {
 * "action": "submitted",
 *     "review": {
 *         "state": "approved",
 *      }
 * }
 */
final readonly class ApprovePrUseCase implements PrEventHandlerInterface
{
    public function __construct(
        private SlackMessageRepositoryInterface $slackMessageRepository,
        private GitHubSlackMappingRepositoryInterface $gitHubSlackMappingRepository,
        private SlackApiClient $slackApiClient,
        private LoggerInterface $logger,
        private string $approvedPrReaction = 'white_check_mark',
    ) {
    }

    public function isApplicable(string $action, array $options = []): bool
    {
        return $action === 'submitted'
            && isset($options['review']['state'])
            && $options['review']['state'] === 'approved';
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

        $botUserId = $this->slackApiClient->getBotUserId();
        if ($botUserId === null) {
            $this->logger->critical('Bot user id not found. Make sure bot is configured properly.');

            return;
        }

        if ($this->isAlreadyApproved($slackMapping, (string) $slackMessage->getTs(), $botUserId)) {
            return;
        }

        // Approve
        $this->slackApiClient->addReaction($slackMapping, (string) $slackMessage->getTs(), $this->approvedPrReaction);
    }

    public function isAlreadyApproved(
        GitHubSlackMapping $slackMapping,
        string $messageTs,
        string $botUserId,
    ): bool {
        $response = $this->slackApiClient->getMessageReactions($slackMapping, $messageTs);

        return array_any(
            $response->data->get('reactions', []),
            fn($reactionItem) => $reactionItem['name'] === $this->approvedPrReaction && in_array(
                $botUserId,
                $reactionItem['users'],
                true
            )
        );
    }
}
