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
            ':rocket: @backend, please review <%s|PR #%s> by %s',
            $webHookTransfer->prUrl,
            $webHookTransfer->prNumber,
            $webHookTransfer->prAuthor
        );

        return $this->post($message);
    }

    #[Override]
    public function updateMessage(WebHookTransfer $webHookTransfer, SlackMessage $slackMessage): array
    {
        $message = sprintf(
            '[%s] ~:rocket: @backend, please review <%s|PR #%s> by %s~',
            $webHookTransfer->isMerged ? 'Merged' : 'Closed',
            $webHookTransfer->prUrl,
            $webHookTransfer->prNumber,
            $webHookTransfer->prAuthor
        );

        $result = $this->post($message, $slackMessage->getTs());
        if (empty($result)) {
            return [];
        }

        $reaction = $webHookTransfer->isMerged ? 'white_check_mark' : 'no_entry_sign';
        $this->addReactionToMessage($slackMessage->getTs(), $reaction);

        return $result;
    }

    /**
     * @return array<string, string>
     */
    private function post(string $message, ?string $ts = null): array
    {
        $url = 'https://slack.com/api/chat.postMessage';
        $payload = [
            'channel' => $this->slackChannel,
            'text'    => $message,
        ];
        if ($ts !== null) {
            $payload['ts'] = $ts;
            $url = 'https://slack.com/api/chat.update';
        }
        $this->logger->info('Payload', $payload);

        try {
            $response = $this->httpClient->request('POST', $url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->slackBotToken,
                    'Content-Type'  => 'application/json',
                ],
                'json'    => $payload,
            ]);
        } catch (\Throwable $throwable) {
            $this->logger->error('Failed to send message to slack', ['exception' => $throwable->getMessage()]);

            return [];
        }

        try {
            $data = $response->toArray();
        } catch (\Throwable $throwable) {
            $this->logger->error('Failed to add reaction to slack message', ['exception' => $throwable->getMessage()]);

            return [];
        }

        if (!$data['ok']) {
            $this->logger->error('Failed response from slack', $data);

            return [];
        }

        $this->logger->info('Slack response', $data);

        return [
            'message' => $data['message']['text'],
            'ts'      => $data['ts'], // Slack timestamp (message ID)
        ];
    }

    public function addReactionToMessage(string $ts, string $emoji): void
    {
        try {
            $response = $this->httpClient->request('POST', 'https://slack.com/api/reactions.add', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->slackBotToken,
                    'Content-Type'  => 'application/json',
                ],
                'json'    => [
                    'channel'   => $this->slackChannel, // Ensure this is a valid channel ID
                    'timestamp' => $ts, // Message timestamp
                    'name'      => $emoji, // Emoji name without colons, e.g., "rocket"
                ],
            ]);
        } catch (\Throwable $throwable) {
            $this->logger->error('Failed to add reaction to slack message', ['exception' => $throwable->getMessage()]);

            return;
        }

        try {
            $data = $response->toArray();
        } catch (\Throwable $throwable) {
            $this->logger->error('Failed to add reaction to slack message', ['exception' => $throwable->getMessage()]);

            return;
        }

        if (!$data['ok']) {
            $this->logger->error('Failed response from slack', $data);
        }
    }
}
