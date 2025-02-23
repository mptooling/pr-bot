<?php

declare(strict_types=1);

namespace App\PullRequest;

use App\Transfers\WebHookTransfer;

final readonly class GithubPullRequestHandler
{
    /**
     * @param iterable<PrEventHandlerInterface> $prEventHandlers
     */
    public function __construct(
        private iterable $prEventHandlers
    ) {
    }

    public function handle(string $action, WebHookTransfer $transfer): void
    {
        foreach ($this->prEventHandlers as $prEventHandler) {
            if ($prEventHandler->isApplicable($action)) {
                $prEventHandler->handle($transfer);
                break;
            }
        }
    }
}
