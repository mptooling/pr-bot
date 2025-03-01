<?php

declare(strict_types=1);

namespace App\PullRequest;

use App\Repository\GitHubSlackMappingRepositoryInterface;
use App\Repository\SlackMessageRepositoryInterface;
use App\Slack\SlackMessengerInterface;
use App\Transfers\WebHookTransfer;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

final readonly class DraftPrUseCase implements PrEventHandlerInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private SlackMessageRepositoryInterface $slackMessageRepository,
        private GitHubSlackMappingRepositoryInterface $gitHubSlackMappingRepository,
        private SlackMessengerInterface $slackMessenger,
        private LoggerInterface $logger,
    ) {
    }

    public function isApplicable(string $action): bool
    {
        return $action === 'converted_to_draft';
    }

    public function handle(WebHookTransfer $webHookTransfer): void
    {
        $slackMessage = $this->slackMessageRepository->findOneByPrNumber($webHookTransfer->prNumber);
        if ($slackMessage === null) {
            return;
        }

        $slackMapping = $this->gitHubSlackMappingRepository->findByRepository($webHookTransfer->repository);
        if ($slackMapping === null) {
            $this->logger->error('Slack mapping not found', ['repository' => $webHookTransfer->repository]);

            return;
        }

        $isRemoved = $this->slackMessenger->removeMessage($slackMessage, $slackMapping);
        if (!$isRemoved) {
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
