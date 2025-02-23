<?php

declare(strict_types=1);

namespace App\PullRequest;

use App\Transfers\WebHookTransfer;

interface PrEventHandlerInterface
{
    public function isApplicable(string $action): bool;
    public function handle(WebHookTransfer $webHookTransfer): void;
}
