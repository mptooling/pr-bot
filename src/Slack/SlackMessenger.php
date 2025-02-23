<?php

declare(strict_types=1);

namespace App\Slack;

use Symfony\Contracts\HttpClient\HttpClientInterface;

final readonly class SlackMessenger implements SlackMessengerInterface
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private string $slackBotToken,
        private string $slackChannel,
    ) {
    }

    public function sendNewMessage(int $prNumber, string $prUrl, string $prAuthor): array
    {
        $message = sprintf(
            'New PR opened by %s: %s',
            $prAuthor,
            $prUrl
        );

        $response = $this->httpClient->request('POST', 'https://slack.com/api/chat.postMessage', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->slackBotToken, // Use Bot Token
                'Content-Type' => 'application/json'
            ],
            'json' => [
                'channel' => $this->slackChannel, // Channel ID (e.g., #backend-reviews)
                'text' => $message
            ]
        ]);

        $data = $response->toArray();

        if (!$data['ok']) {
            throw new \Exception('Slack API Error: ' . $data['error']);
        }

        return [
            'message' => $data['message']['text'],
            'ts' => $data['ts'] // Slack timestamp (message ID)
        ];
    }
}
