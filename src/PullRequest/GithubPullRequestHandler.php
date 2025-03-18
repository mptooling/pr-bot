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

    /**
     * @param string $action
     * @param WebHookTransfer $transfer
     * @param array<array-key, mixed> $options
     *
     * @return void
     */
    public function handle(string $action, WebHookTransfer $transfer, array $options = []): void
    {
        foreach ($this->prEventHandlers as $prEventHandler) {
            if ($prEventHandler->isApplicable($action, $options)) {
                $prEventHandler->handle($transfer);
                break;
            }
        }
    }
}
