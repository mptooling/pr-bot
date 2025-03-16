<?php

declare(strict_types=1);

namespace App\Slack;

use App\Entity\GitHubSlackMapping;
use App\Transfers\WebHookTransfer;

readonly class SlackMessageComposer
{
    public function __construct(
        private string $slackReactionNewPr = 'rocket',
    ) {
    }

    public function composeUpdatedMessage(
        WebHookTransfer $webHookTransfer,
        GitHubSlackMapping $slackMapping
    ): string {
        return sprintf(
            '[%s] ~%s~',
            $webHookTransfer->isMerged ? 'Merged' : 'Closed',
            $this->composeNewSlackMessage($webHookTransfer, $slackMapping),
        );
    }

    public function composeNewSlackMessage(WebHookTransfer $webHookTransfer, GitHubSlackMapping $slackMapping): string
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
