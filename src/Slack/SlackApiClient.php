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

    public function updateChatMessage(GitHubSlackMapping $slackMapping, string $ts, string $message): SlackResponse
    {
        $payload = [
            'channel' => $slackMapping->getSlackChannel(),
            'text'    => $message,
            'ts'      => $ts,
        ];

        $this->logger->debug('Payload', $payload);

        try {
            $response = $this->httpClient->request('POST', 'https://slack.com/api/chat.update', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->slackBotToken,
                    'Content-Type'  => 'application/json',
                ],
                'json'    => $payload,
            ]);
        } catch (Throwable $throwable) {
            $this->logger->error('Failed to send message to slack', ['exception' => $throwable->getMessage()]);

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

        $this->logger->debug('[Update Message] Slack response', $data);

        return new SlackResponse(slackMessageId: $data['ts']);
    }

    public function removeSlackMessage(SlackMessage $slackMessage, GitHubSlackMapping $slackMapping): SlackResponse
    {
        try {
            $response = $this->httpClient->request('POST', 'https://slack.com/api/chat.delete', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->slackBotToken,
                    'Content-Type'  => 'application/json',
                ],
                'json'    => [
                    'channel' => $slackMapping->getSlackChannel(),
                    'ts'      => $slackMessage->getTs(),
                ],
            ]);
        } catch (Throwable $throwable) {
            $this->logger->error(
                'Failed to remove slack message',
                ['data' => $slackMessage, 'exception' => $throwable->getMessage()]
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
            $this->logger->error('Failed to remove slack message', $data);

            return SlackResponse::fail();
        }

        $this->logger->debug('[Remove Message] Slack response', $data);

        return new SlackResponse();
    }

    public function addReaction(GitHubSlackMapping $slackMapping, string $ts, string $emoji): SlackResponse
    {
        try {
            $response = $this->httpClient->request('POST', 'https://slack.com/api/reactions.add', [
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
        } catch (Throwable $throwable) {
            $this->logger->error('Failed to add reaction to slack message', ['exception' => $throwable->getMessage()]);

            return SlackResponse::fail();
        }

        try {
            $data = $response->toArray();
        } catch (Throwable $throwable) {
            $this->logger->error('Failed to add reaction to slack message', ['exception' => $throwable->getMessage()]);

            return SlackResponse::fail();
        }

        if (!$data['ok']) {
            $this->logger->error('Failed response from slack', $data);
        }

        $this->logger->debug('[Add Reaction] Slack response', $data);

        return new SlackResponse();
    }
}
