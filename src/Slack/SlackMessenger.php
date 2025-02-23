<?php

declare(strict_types=1);

namespace App\Slack;

use App\Entity\SlackMessage;
use App\Transfers\WebHookTransfer;
use Override;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final readonly class SlackMessenger implements SlackMessengerInterface
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private LoggerInterface $logger,
        private string $slackBotToken,
        private string $slackChannel,
    ) {
    }

    #[Override]
    public function sendNewMessage(WebHookTransfer $webHookTransfer): array
    {
        $message = sprintf(
            'New PR opened by %s: %s',
            $webHookTransfer->prNumber,
            $webHookTransfer->prAuthor
        );

        return $this->post($message);
    }

    #[Override]
    public function updateMessage(WebHookTransfer $webHookTransfer, SlackMessage $slackMessage): array
    {
        $message = sprintf(
            '[Closed] ~New PR opened by %s: %s~',
            $webHookTransfer->prNumber,
            $webHookTransfer->prAuthor
        );

        return $this->post($message, $slackMessage->getTs());
    }

    /**
     * @return array<string, string>
     */
    private function post(string $message, ?string $ts = null): array
    {
        $payload = [
            'channel' => $this->slackChannel,
            'text'    => $message,
        ];
        if ($ts !== null) {
            $payload['ts'] = sprintf("'%s'", $ts);
        }
        $response = $this->httpClient->request('POST', 'https://slack.com/api/chat.postMessage', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->slackBotToken,
                'Content-Type'  => 'application/json',
            ],
            'json'    => $payload,
        ]);

        $data = $response->toArray();
        $this->logger->info('Slack response', $data);

        if (!$data['ok']) {
            return [];
        }

        return [
            'message' => $data['message']['text'],
            'ts'      => $data['ts'], // Slack timestamp (message ID)
        ];
    }
}
