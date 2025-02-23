<?php

declare(strict_types=1);

namespace App\Controller;

use App\PullRequest\ClosePrUseCase;
use App\PullRequest\OpenPrUseCase;
use App\Transfers\WebHookTransfer;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

final class GitHubWebhookController
{
    public function __construct(
        private OpenPrUseCase $prOpenedUseCase,
        private ClosePrUseCase $prClosedUseCase,
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

        if ($action === 'opened') {
            $this->prOpenedUseCase->handle(new WebHookTransfer($prNumber, $prUrl, $prAuthor));

            return new JsonResponse("ok");
        }

        if ($action === 'closed') {
            $isMerged = $data['pull_request']['merged'] ?? false;
            $this->prClosedUseCase->handle(new WebHookTransfer($prNumber, $prUrl, $prAuthor, $isMerged));

            return new JsonResponse("ok");
        }

        return new JsonResponse(['message' => 'No action taken']);
    }
}
