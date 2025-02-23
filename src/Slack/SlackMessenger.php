<?php

declare(strict_types=1);

namespace App\Slack;

use App\Transfers\WebHookTransfer;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final readonly class SlackMessenger implements SlackMessengerInterface
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private string $slackBotToken,
        private string $slackChannel,
    ) {
    }

    public function sendNewMessage(WebHookTransfer $webHookTransfer): array
    {
        $message = sprintf(
            'New PR opened by %s: %s',
            $webHookTransfer->prNumber,
            $webHookTransfer->prAuthor
        );

        $response = $this->httpClient->request('POST', 'https://slack.com/api/chat.postMessage', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->slackBotToken,
                'Content-Type'  => 'application/json',
            ],
            'json'    => [
                'channel' => $this->slackChannel,
                'text'    => $message,
            ],
        ]);

        $data = $response->toArray();

        if (!$data['ok']) {
            throw new \Exception('Slack API Error: ' . $data['error']);
        }

        return [
            'message' => $data['message']['text'],
            'ts'      => $data['ts'], // Slack timestamp (message ID)
        ];
    }
}
