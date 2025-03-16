<?php

declare(strict_types=1);

namespace App\Slack;

final readonly class SlackResponse
{
    public function __construct(
        public ?string $slackMessageId = null,
        public bool $isSuccessful = true,
    ) {
    }

    public static function fail(): self
    {
        return new self(isSuccessful:false);
    }
}
