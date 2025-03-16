<?php

declare(strict_types=1);

namespace App\PullRequest;

use App\Entity\SlackMessage;
use App\Repository\GitHubSlackMappingRepositoryInterface;
use App\Repository\SlackMessageRepositoryInterface;
use App\Slack\SlackApiClient;
use App\Slack\SlackMessageComposer;
use App\Transfers\WebHookTransfer;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

final readonly class OpenPrUseCase implements PrEventHandlerInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
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

        $entity = new SlackMessage(); // todo :: move to repository
        $entity->setPrNumber($webHookTransfer->prNumber)
            ->setGhRepository($webHookTransfer->repository)
            ->setTs($slackResponse->slackMessageId);

        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        $this->logger->info('Slack message sent', ['prNumber' => $webHookTransfer->prNumber]);
    }

    public function isApplicable(string $action): bool
    {
        return $action === 'opened' || $action === 'ready_for_review';
    }
}
