<?php

declare(strict_types=1);

namespace App\Slack;

use App\Entity\GitHubSlackMapping;
use App\Entity\SlackMessage;
use App\Transfers\WebHookTransfer;
use Override;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Throwable;

final readonly class SlackMessenger implements SlackMessengerInterface
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private LoggerInterface $logger,
        private string $slackBotToken,
        private bool $withReactions = false,
        private string $slackReactionNewPr = 'rocket',
        private string $slackReactionMergedPr = 'white_check_mark',
        private string $slackReactionClosedPr = 'no_entry_sign',
    ) {
    }

    #[Override]
    public function sendNewMessage(WebHookTransfer $webHookTransfer, GitHubSlackMapping $slackMapping): array
    {
        $message = $this->composeNewSlackMessage($webHookTransfer, $slackMapping);

        return $this->post($message, $slackMapping);
    }

    #[Override]
    public function updateMessage(
        WebHookTransfer $webHookTransfer,
        SlackMessage $slackMessage,
        GitHubSlackMapping $slackMapping
    ): array {
        $message = sprintf(
            '[%s] ~%s~',
            $webHookTransfer->isMerged ? 'Merged' : 'Closed',
            $this->composeNewSlackMessage($webHookTransfer, $slackMapping),
        );

        $result = $this->post($message, $slackMapping, $slackMessage->getTs());
        if (empty($result)) {
            return [];
        }

        $reaction = $webHookTransfer->isMerged ? $this->slackReactionMergedPr : $this->slackReactionClosedPr;

        if ($this->withReactions) {
            $this->addReactionToMessage((string)$slackMessage->getTs(), $reaction, $slackMapping);
        }

        return $result;
    }

    /**
     * @return array<string, string>
     */
    private function post(string $message, GitHubSlackMapping $slackMapping, ?string $ts = null): array
    {
        $url = 'https://slack.com/api/chat.postMessage';
        $payload = [
            'channel' => $slackMapping->getSlackChannel(),
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
        } catch (Throwable $throwable) {
            $this->logger->error('Failed to send message to slack', ['exception' => $throwable->getMessage()]);

            return [];
        }

        try {
            $data = $response->toArray();
        } catch (Throwable $throwable) {
            $this->logger->error('Failed to add reaction to slack message', ['exception' => $throwable->getMessage()]);

            return [];
        }

        if (!$data['ok']) {
            $this->logger->error('Failed response from slack', $data);

            return [];
        }

        $this->logger->debug('[Create|Update Message] Slack response', $data);

        return [
            'message' => $data['message']['text'],
            'ts'      => $data['ts'], // Slack timestamp (message ID)
        ];
    }

    private function addReactionToMessage(string $ts, string $emoji, GitHubSlackMapping $slackMapping): void
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

            return;
        }

        try {
            $data = $response->toArray();
        } catch (Throwable $throwable) {
            $this->logger->error('Failed to add reaction to slack message', ['exception' => $throwable->getMessage()]);

            return;
        }

        if (!$data['ok']) {
            $this->logger->error('Failed response from slack', $data);
        }

        $this->logger->debug('[Add Reaction] Slack response', $data);
    }

    public function removeMessage(SlackMessage $slackMessage, GitHubSlackMapping $slackMapping): bool
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

            return false;
        }

        try {
            $data = $response->toArray();
        } catch (Throwable $throwable) {
            $this->logger->error(
                'Failed to remove slack message',
                ['data' => $slackMessage, 'exception' => $throwable->getMessage()]
            );

            return false;
        }

        if (!$data['ok']) {
            $this->logger->error('Failed to remove slack message', $data);

            return false;
        }

        $this->logger->debug('[Remove Message] Slack response', $data);

        return true;
    }

    /**
     * @param WebHookTransfer $webHookTransfer
     *
     * @return string
     */
    private function composeNewSlackMessage(WebHookTransfer $webHookTransfer, GitHubSlackMapping $slackMapping): string
    {
        return sprintf(
            ':%s: %s, please review <%s|PR #%d: %s> by %s',
            $this->slackReactionNewPr,
            implode(',', $slackMapping->getMentions()),
            $webHookTransfer->prUrl,
            $webHookTransfer->prNumber,
            $webHookTransfer->prTitle,
            $webHookTransfer->prAuthor
        );
    }
}
