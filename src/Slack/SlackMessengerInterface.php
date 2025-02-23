<?php

declare(strict_types=1);

namespace App\Slack;

use App\Transfers\WebHookTransfer;

interface SlackMessengerInterface
{
    /**
     * @return array<mixed>
     */
    public function sendNewMessage(WebHookTransfer $webHookTransfer): array;
}
