<?php

declare(strict_types=1);

namespace App\Slack;

use App\Entity\GitHubSlackMapping;
use App\Entity\SlackMessage;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Throwable;

readonly class SlackApiClient
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private LoggerInterface $logger,
        private string $slackBotToken,
    ) {
    }

    public function postChatMessage(string $message, GitHubSlackMapping $slackMapping): SlackResponse
    {
        $responseData = $this->request('POST', 'https://slack.com/api/chat.postMessage', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->slackBotToken,
                'Content-Type'  => 'application/json',
            ],
            'json'    => [
                'channel' => $slackMapping->getSlackChannel(),
                'text'    => $message,
            ],
        ]);

        if ($responseData === []) {
            return SlackResponse::fail();
        }

        return new SlackResponse($responseData['ts']);
    }

    public function updateChatMessage(GitHubSlackMapping $slackMapping, string $ts, string $message): SlackResponse
    {
        $responseData = $this->request('POST', 'https://slack.com/api/chat.update', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->slackBotToken,
                'Content-Type'  => 'application/json',
            ],
            'json'    => [
                'channel' => $slackMapping->getSlackChannel(),
                'text'    => $message,
                'ts'      => $ts,
            ],
        ]);

        if ($responseData === []) {
            return SlackResponse::fail();
        }

        return new SlackResponse(slackMessageId: $responseData['ts']);
    }

    public function removeSlackMessage(SlackMessage $slackMessage, GitHubSlackMapping $slackMapping): SlackResponse
    {
        $responseData = $this->request('POST', 'https://slack.com/api/chat.delete', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->slackBotToken,
                'Content-Type'  => 'application/json',
            ],
            'json'    => [
                'channel' => $slackMapping->getSlackChannel(),
                'ts'      => $slackMessage->getTs(),
            ],
        ]);


        if ($responseData === []) {
            return SlackResponse::fail();
        }

        return new SlackResponse();
    }

    public function addReaction(GitHubSlackMapping $slackMapping, string $ts, string $emoji): SlackResponse
    {
        $responseData = $this->request('POST', 'https://slack.com/api/reactions.add', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->slackBotToken,
                'Content-Type'  => 'application/json',
            ],
            'json'    => [
                'channel' => $slackMapping->getSlackChannel(),
                'timestamp' => $ts,
                'name' => $emoji,
            ],
        ]);

        if ($responseData === []) {
            return SlackResponse::fail();
        }

        return new SlackResponse();
    }

    public function getMessageReactions(GitHubSlackMapping $slackMapping, string $messageTs): SlackResponse
    {
        $responseData = $this->request('GET', 'https://slack.com/api/reactions.get', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->slackBotToken,
                'Content-Type' => 'application/json',
            ],
            'query' => [
                'channel' => $slackMapping->getSlackChannel(),
                'timestamp' => $messageTs,
            ],
        ]);

        if ($responseData === []) {
            return SlackResponse::fail();
        }

        $slackResponse = new SlackResponse();
        if (!isset($responseData['message']['reactions'])) {
            return $slackResponse;
        }

        $slackResponse->data->add(['reactions' => $responseData['message']['reactions']]);

        return $slackResponse;
    }

    /**
     * @param array<string, mixed> $options
     *
     * @return array<array-key, mixed>
     */
    private function request(
        string $method,
        string $url,
        array $options
    ): array {
        $this->logger->debug('Slack API Request', ['method' => $method, 'url' => $url, 'options' => $options]);
        try {
            $response = $this->httpClient->request($method, $url, $options);
        } catch (Throwable $throwable) {
            $this->logger->error('Slack API Request failed', [
                'method' => $method,
                'url' => $url,
                'options' => $options,
                'exception' => $throwable->getMessage()
            ]);

            return [];
        }

        try {
            $data = $response->toArray();
        } catch (Throwable $throwable) {
            $this->logger->error('Failed to extract data message', ['exception' => $throwable->getMessage()]);

            return [];
        }
        if (!$data['ok']) {
            $this->logger->error('Failed response from slack', $data);

            return [];
        }

        $this->logger->debug('Slack API Response data', $data);

        return $data;
    }

    public function getBotUserId(): ?string
    {
        $responseData = $this->request('GET', 'https://slack.com/api/auth.test', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->slackBotToken,
                'Content-Type' => 'application/json',
            ],
        ]);

        return $responseData['user_id'] ?? null;
    }
}
