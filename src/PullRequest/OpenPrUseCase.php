<?php

declare(strict_types=1);

namespace App\PullRequest;

use App\Entity\SlackMessage;
use App\Repository\SlackMessageRepositoryInterface;
use App\Slack\SlackMessengerInterface;
use App\Transfers\WebHookTransfer;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

final readonly class OpenPrUseCase implements PrEventHandlerInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private SlackMessageRepositoryInterface $slackMessageRepository,
        private SlackMessengerInterface $slackMessenger,
        private LoggerInterface $logger,
    ) {
    }

    public function handle(WebHookTransfer $webHookTransfer): void
    {
        $message = $this->slackMessageRepository->findOneByPrNumber($webHookTransfer->prNumber);
        if ($message !== null) {
            $this->logger->info('Message already sent', ['prNumber' => $webHookTransfer->prNumber]);

            return;
        }

        $slackResponse = $this->slackMessenger->sendNewMessage($webHookTransfer);
        if (!isset($slackResponse['ts'])) {
            $this->logger->error('Slack message not sent', [
                'prNumber' => $webHookTransfer->prNumber,
                'response' => $slackResponse,
            ]);

            return;
        }

        $entity = new SlackMessage();
        $entity->setPrNumber($webHookTransfer->prNumber)
            ->setTs($slackResponse['ts']);

        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        $this->logger->info('Slack message sent', ['prNumber' => $webHookTransfer->prNumber]);
    }

    public function isApplicable(string $action): bool
    {
        return $action === 'opened' || $action === 'ready_for_review';
    }
}
