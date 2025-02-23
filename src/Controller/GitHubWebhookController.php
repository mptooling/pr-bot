<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\SlackMessage;
use App\Slack\SlackMessengerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;

final class GitHubWebhookController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private SlackMessengerInterface $slackMessenger,
        private string $githubWebhookSecret
    ) {
    }

    #[Route('/webhook/github', name: 'github_webhook', methods: ['POST'])]
    public function handleWebhook(Request $request): JsonResponse
    {
        $response = $this->verifySignature($request);
        if ($response !== null) {
            return $response;
        }

        // Parse JSON
        $data = json_decode($request->getContent(), true);
        $action = $data['action'] ?? null;
        $prNumber = $data['pull_request']['number'] ?? null;
        $prUrl = $data['pull_request']['html_url'] ?? '';
        $prAuthor = $data['pull_request']['user']['login'] ?? '';

        if (!$prNumber) {
            return new JsonResponse(['error' => 'No PR number found'], JsonResponse::HTTP_BAD_REQUEST);
        }

        // Handle PR Opened
        if ($action === 'opened') {
            return $this->handlePROpened($prNumber, $prUrl, $prAuthor);
        }

        return new JsonResponse(['message' => 'No action taken']);
    }

    private function handlePROpened(int $prNumber, string $prUrl, string $prAuthor): JsonResponse
    {
        $slackResponse = $this->slackMessenger->sendNewMessage($prNumber, $prUrl, $prAuthor);
        $response = new JsonResponse(['message' => "Slack message sent for PR #$prNumber"]);

        if (!isset($slackResponse['ts'])) {
            return $response;
        }

        $entity = new SlackMessage();
        $entity->setPrNumber($prNumber)
            ->setTs($slackResponse['ts']);

        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        return $response;
    }

    private function verifySignature(Request $request): ?JsonResponse
    {
        // Get GitHub Signature
        $signature = $request->headers->get('X-Hub-Signature-256');
        if (!$signature) {
            return new JsonResponse(['error' => 'no signature'], JsonResponse::HTTP_FORBIDDEN);
        }

        $expected = "sha256=" . hash_hmac('sha256', $request->getContent(), $this->githubWebhookSecret);
        if (!hash_equals($expected, $signature)) {
            return new JsonResponse(['error' => 'Invalid signature'], JsonResponse::HTTP_FORBIDDEN);
        }

        return null;
    }
}
