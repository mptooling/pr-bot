<?php

declare(strict_types=1);

namespace App\Slack;

use App\Entity\GitHubSlackMapping;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Throwable;

final readonly class SlackApiClient
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private LoggerInterface $logger,
        private string $slackBotToken,
    ) {
    }

    public function postChatMessage(string $message, GitHubSlackMapping $slackMapping): SlackResponse
    {
        $payload = [
            'channel' => $slackMapping->getSlackChannel(),
            'text'    => $message,
        ];
        $this->logger->debug('Payload', $payload);

        try {
            $response = $this->httpClient->request('POST', 'https://slack.com/api/chat.postMessage', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->slackBotToken,
                    'Content-Type'  => 'application/json',
                ],
                'json'    => $payload,
            ]);
        } catch (Throwable $throwable) {
            $this->logger->error(
                'Failed to send message to slack',
                ['exception' => $throwable->getMessage()]
            );

            return SlackResponse::fail();
        }

        try {
            $data = $response->toArray();
        } catch (Throwable $throwable) {
            $this->logger->error(
                'Failed to extract daya from slack response',
                ['exception' => $throwable->getMessage()]
            );

            return SlackResponse::fail();
        }

        if (!$data['ok']) {
            $this->logger->error('Failed response from slack', $data);

            return SlackResponse::fail();
        }

        $this->logger->debug('[Create Message] Slack response', $data);

        return new SlackResponse($data['ts']);
    }
}
