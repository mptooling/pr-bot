<?php

declare(strict_types=1);

namespace App\Slack;

interface SlackMessengerInterface
{
    /**
     * @param int $prNumber
     * @param string $prUrl
     * @param string $prAuthor
     *
     * @return array<mixed>
     */
    public function sendNewMessage(int $prNumber, string $prUrl, string $prAuthor): array;
}
