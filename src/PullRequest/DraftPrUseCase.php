<?php

declare(strict_types=1);

namespace App\PullRequest;

use App\Repository\GitHubSlackMappingRepositoryInterface;
use App\Repository\SlackMessageRepositoryInterface;
use App\Slack\SlackApiClient;
use App\Transfers\WebHookTransfer;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

final readonly class DraftPrUseCase implements PrEventHandlerInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private SlackMessageRepositoryInterface $slackMessageRepository,
        private GitHubSlackMappingRepositoryInterface $gitHubSlackMappingRepository,
        private LoggerInterface $logger,
        private SlackApiClient $slackApiClient,
    ) {
    }

    public function isApplicable(string $action, array $options = []): bool
    {
        return $action === 'converted_to_draft';
    }

    public function handle(WebHookTransfer $webHookTransfer): void
    {
        $slackMessage = $this->slackMessageRepository->findOneByPrNumberAndRepository(
            $webHookTransfer->prNumber,
            $webHookTransfer->repository,
        );

        if ($slackMessage === null) {
            return;
        }

        $slackMapping = $this->gitHubSlackMappingRepository->findByRepository($webHookTransfer->repository);
        if ($slackMapping === null) {
            $this->logger->error('Slack mapping not found', ['repository' => $webHookTransfer->repository]);

            return;
        }

        $slackResponse = $this->slackApiClient->removeSlackMessage($slackMessage, $slackMapping);
        // todo :: handle cases when message is not removed from slack because it is not found
        if (!$slackResponse->isSuccessful) {
            return;
        }

        $this->entityManager->remove($slackMessage);
        $this->entityManager->flush();

        $this->logger->info(
            'Slack slackMessage removed as it the became draft',
            ['prNumber' => $webHookTransfer->prNumber]
        );
    }
}
