<?php

declare(strict_types=1);

namespace App\PullRequest;

use App\Repository\GitHubSlackMappingRepositoryInterface;
use App\Repository\SlackMessageRepositoryInterface;
use App\Slack\SlackApiClient;
use App\Transfers\WebHookTransfer;
use Psr\Log\LoggerInterface;

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

    public function isApplicable(string $action): bool
    {
        return $action === 'approved';
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

        $this->slackApiClient->addReaction(
            $slackMapping,
            (string) $slackMessage->getTs(),
            $this->approvedPrReaction,
        );
    }
}
