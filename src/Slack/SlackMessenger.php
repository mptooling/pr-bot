<?php

declare(strict_types=1);

namespace App\Slack;

use Symfony\Contracts\HttpClient\HttpClientInterface;

final readonly class SlackMessenger implements SlackMessengerInterface
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private string $slackWebhookUrl,
    ) {
    }

    public function sendNewMessage(int $prNumber, string $prUrl, string $prAuthor): array
    {
        return ['ts' => '12345'];
        $message = sprintf(
            'New PR opened by %s: %s',
            $prAuthor,
            $prUrl
        );

        $response = $this->httpClient->request('POST', $this->slackWebhookUrl, [
            'json' => [
                'text' => $message
            ]
        ]);

        return $response->toArray();
    }
}
