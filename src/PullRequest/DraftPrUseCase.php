<?php

declare(strict_types=1);

namespace App\PullRequest;

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
        private SlackMessengerInterface $slackMessenger,
        private LoggerInterface $logger,
    ) {
    }

    public function isApplicable(string $action): bool
    {
        return $action === 'draft';
    }

    public function handle(WebHookTransfer $webHookTransfer): void
    {
        // TODO: Implement handle() method.
    }
}
