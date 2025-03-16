<?php

declare(strict_types=1);

namespace App\Slack;

use App\Entity\GitHubSlackMapping;
use App\Entity\SlackMessage;
use App\Transfers\WebHookTransfer;

interface SlackMessengerInterface
{
    public function sendNewMessage(WebHookTransfer $webHookTransfer, GitHubSlackMapping $slackMapping): SlackResponse;

    public function updateMessage(
        WebHookTransfer $webHookTransfer,
        SlackMessage $slackMessage,
        GitHubSlackMapping $slackMapping
    ): SlackResponse;

    public function removeMessage(SlackMessage $slackMessage, GitHubSlackMapping $slackMapping): bool;
}
