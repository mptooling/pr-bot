<?php

declare(strict_types=1);

namespace App\Slack;

use Symfony\Component\HttpFoundation\ParameterBag;

final readonly class SlackResponse
{
    public ParameterBag $data;

    public function __construct(
        public ?string $slackMessageId = null,
        public bool $isSuccessful = true,
    ) {
        $this->data = new ParameterBag();
    }

    public static function fail(): self
    {
        return new self(isSuccessful:false);
    }
}
