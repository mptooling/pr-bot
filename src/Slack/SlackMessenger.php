<?php

declare(strict_types=1);

namespace App\Slack;

use App\Entity\GitHubSlackMapping;
use App\Entity\SlackMessage;
use App\Transfers\WebHookTransfer;
use Override;
use Psr\Log\LoggerInterface;
use Throwable;

final readonly class SlackMessenger implements SlackMessengerInterface
{
    public function __construct(
        private SlackApiClient $slackApiClient,
        private LoggerInterface $logger,
        private bool $withReactions = false,
        private string $slackReactionNewPr = 'rocket',
        private string $slackReactionMergedPr = 'white_check_mark',
        private string $slackReactionClosedPr = 'no_entry_sign',
    ) {
    }

    #[Override]
    public function sendNewMessage(WebHookTransfer $webHookTransfer, GitHubSlackMapping $slackMapping): SlackResponse
    {
        $message = $this->composeNewSlackMessage($webHookTransfer, $slackMapping);

        return $this->slackApiClient->postChatMessage($message, $slackMapping);
    }

    #[Override]
    public function updateMessage(
        WebHookTransfer $webHookTransfer,
        SlackMessage $slackMessage,
        GitHubSlackMapping $slackMapping
    ): SlackResponse {
        $message = sprintf(
            '[%s] ~%s~',
            $webHookTransfer->isMerged ? 'Merged' : 'Closed',
            $this->composeNewSlackMessage($webHookTransfer, $slackMapping),
        );

        $response = $this->slackApiClient->updateChatMessage($slackMapping, $slackMessage->getTs(), $message);
        if (!$response->isSuccessful) {
            return $response;
        }

        $reaction = $webHookTransfer->isMerged ? $this->slackReactionMergedPr : $this->slackReactionClosedPr;

        if ($this->withReactions) {
            $this->addReactionToMessage((string)$slackMessage->getTs(), $reaction, $slackMapping);
        }

        return $response;
    }

    private function addReactionToMessage(string $ts, string $emoji, GitHubSlackMapping $slackMapping): void
    {
        $this->slackApiClient->addReaction($slackMapping, $ts, $emoji);
    }

    public function removeMessage(SlackMessage $slackMessage, GitHubSlackMapping $slackMapping): bool
    {
        $response = $this->slackApiClient->removeSlackMessage($slackMessage, $slackMapping);

        return $response->isSuccessful;
    }

    /**
     * @param WebHookTransfer $webHookTransfer
     *
     * @return string
     */
    private function composeNewSlackMessage(WebHookTransfer $webHookTransfer, GitHubSlackMapping $slackMapping): string
    {
        return sprintf(
            ':%s: %s, please review <%s|PR #%d: %s> by %s',
            $this->slackReactionNewPr,
            implode(',', $slackMapping->getMentions()),
            $webHookTransfer->prUrl,
            $webHookTransfer->prNumber,
            $webHookTransfer->prTitle,
            $webHookTransfer->prAuthor
        );
    }
}
