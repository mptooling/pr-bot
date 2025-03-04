<?php

declare(strict_types=1);

namespace App\Controller;

use App\PullRequest\GithubPullRequestHandler;
use App\Transfers\WebHookTransfer;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final readonly class GitHubWebhookController
{
    public function __construct(
        private GithubPullRequestHandler $handler,
        private LoggerInterface $logger,
    ) {
    }

    #[Route('/webhook/github', name: 'github_webhook', methods: ['POST'])]
    public function handleWebhook(Request $request): JsonResponse
    {
        // Parse JSON
        $data = json_decode($request->getContent(), true);
        $action = $data['action'] ?? '';
        $prNumber = $data['pull_request']['number'] ?? 0;
        $prTitle = $data['pull_request']['title'] ?? '';
        $prUrl = $data['pull_request']['html_url'] ?? '';
        $prAuthor = $data['pull_request']['user']['login'] ?? '';
        $repository = $data['repository']['full_name'];

        if (!$prNumber) {
            return new JsonResponse(['error' => 'No PR number found'], Response::HTTP_BAD_REQUEST);
        }

        $this->logger->debug('Received webhook', $data);

        $transfer = new WebHookTransfer(
            repository: $repository,
            prNumber: $prNumber,
            prTitle: $prTitle,
            prUrl: $prUrl,
            prAuthor: $prAuthor,
            isMerged:  $data['pull_request']['merged'] ?? false
        );

        $this->handler->handle($action, $transfer);

        return new JsonResponse("ok");
    }
}
