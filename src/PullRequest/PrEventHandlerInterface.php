<?php

declare(strict_types=1);

namespace App\PullRequest;

use App\Transfers\WebHookTransfer;

interface PrEventHandlerInterface
{
    /**
     * @param string $action
     * @param array<array-key, mixed> $options
     *
     * @return bool
     */
    public function isApplicable(string $action, array $options = []): bool;
    public function handle(WebHookTransfer $webHookTransfer): void;
}
