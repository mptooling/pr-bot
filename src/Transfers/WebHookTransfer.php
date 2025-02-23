<?php

declare(strict_types=1);

namespace App\Transfers;

final readonly class WebHookTransfer
{
    public function __construct(
        public int $prNumber,
        public string $prUrl,
        public string $prAuthor,
        public bool $isMerged = false,
    ) {
    }
}
