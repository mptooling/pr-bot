<?php

declare(strict_types=1);

namespace App\Controller;

use App\PullRequest\ClosePrUseCase;
use App\PullRequest\GithubPullRequestHandler;
use App\PullRequest\OpenPrUseCase;
use App\Transfers\WebHookTransfer;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

final class GitHubWebhookController
{
    public function __construct(
        private GithubPullRequestHandler $handler,
    ) {
    }

    #[Route('/webhook/github', name: 'github_webhook', methods: ['POST'])]
    public function handleWebhook(Request $request): JsonResponse
    {
        // Parse JSON
        $data = json_decode($request->getContent(), true);
        $action = $data['action'] ?? null;
        $prNumber = $data['pull_request']['number'] ?? null;
        $prUrl = $data['pull_request']['html_url'] ?? '';
        $prAuthor = $data['pull_request']['user']['login'] ?? '';
        $isDraft = $data['pull_request']['draft'] ?? false; // Check if it's a draft PR
        if ($isDraft) {
            return new JsonResponse(['message' => 'Draft PRs are ignored']);
        }
        if (!$prNumber) {
            return new JsonResponse(['error' => 'No PR number found'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $transfer = new WebHookTransfer($prNumber, $prUrl, $prAuthor, $data['pull_request']['merged'] ?? false);
        $this->handler->handle($action, $transfer);


        return new JsonResponse("ok");
    }
}
