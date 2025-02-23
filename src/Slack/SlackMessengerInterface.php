<?php

declare(strict_types=1);

namespace App\Slack;

use App\Entity\SlackMessage;
use App\Transfers\WebHookTransfer;

interface SlackMessengerInterface
{
    /**
     * @return array<string, string>
     */
    public function sendNewMessage(WebHookTransfer $webHookTransfer): array;

    /**
     * @return array<string, string>
     */
    public function updateMessage(WebHookTransfer $webHookTransfer, SlackMessage $slackMessage): array;

    public function removeMessage(SlackMessage $slackMessage): bool;
}
