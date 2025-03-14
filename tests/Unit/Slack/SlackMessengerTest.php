<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack;

use App\Entity\GitHubSlackMapping;
use App\Entity\SlackMessage;
use App\Slack\SlackMessenger;
use App\Slack\SlackMessengerInterface;
use App\Transfers\WebHookTransfer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class SlackMessengerTest extends TestCase
{
    private HttpClientInterface|MockObject $httpClient;
    private LoggerInterface|MockObject $logger;
    private SlackMessengerInterface $slackMessenger;

    protected function setUp(): void
    {
        $this->httpClient = $this->createMock(HttpClientInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->slackMessenger = new SlackMessenger(
            $this->httpClient,
            $this->logger,
            slackBotToken: 'test-slack-bot-token',
            withReactions: true,
        );
    }

    public function testSendNewMessageSendsCorrectPayload(): void
    {
         // Arrange & Assert
        $responseData = [
            'ok' => true,
            'message' => [
                'text' => ':rocket: @channel, please review <http://example.com/pr/1|PR #1> by author'
            ],
            'ts' => '1234567890.123456'
        ];

        $expectedResult = [
            'message' => $responseData['message']['text'],
            'ts' => $responseData['ts'],
        ];

        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock->expects($this->once())
            ->method('toArray')
            ->willReturn($responseData);

        $this->httpClient->expects($this->once())
            ->method('request')
            ->willReturn($responseMock);

        $webHookTransfer = new WebHookTransfer(
            repository: 'test-github-repository',
            prNumber: 1,
            prTitle: 'The title',
            prUrl: 'http://example.com/pr/1',
            prAuthor: 'author',
            isMerged: false
        );

        $slackMapping = new GitHubSlackMapping()
            ->setSlackChannel('test-slack-channel')
            ->setRepository('test-github-repository')
            ->setMentions(['rocket']);

        // Act
        $result = $this->slackMessenger->sendNewMessage($webHookTransfer, $slackMapping);

        // Assert
        $this->assertEquals($expectedResult, $result);
    }

    public function testUpdateMessageUpdatesCorrectPayloadWithReaction(): void
    {
        // Arrange & Assert
        $webHookTransfer = new WebHookTransfer(
            repository: 'test-github-repository',
            prNumber: 1,
            prTitle: 'The title',
            prUrl: 'http://example.com/pr/1',
            prAuthor: 'author',
            isMerged: true
        );

        $slackMessage = new SlackMessage();
        $slackMessage->setTs('1234567890.123456');

        $responseData = [
            'ok' => true,
            'message' => [
                'text' => ':rocket: @channel, please review <http://example.com/pr/1|PR #1> by author'
            ],
            'ts' => '1234567890.123456'
        ];

        $expectedResult = [
            'message' => $responseData['message']['text'],
            'ts' => $responseData['ts'],
        ];

        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock->expects($this->once())
            ->method('toArray')
            ->willReturn($responseData);

        $reactionResponse = $this->createMock(ResponseInterface::class);
        $reactionResponse->expects($this->once())
            ->method('toArray')
            ->willReturn(['ok' => true]);

        $this->httpClient->expects($this->exactly(2))
            ->method('request')
            ->willReturnCallback(function (string $method, string $url) use ($responseMock, $reactionResponse) {
                if ($method === 'POST' && $url === 'https://slack.com/api/chat.update') {
                    return $responseMock;
                }

                return $reactionResponse;
            });

        $slackMapping = new GitHubSlackMapping()
            ->setSlackChannel('test-slack-channel')
            ->setRepository('test-github-repository')
            ->setMentions(['rocket']);

        // Act
        $result = $this->slackMessenger->updateMessage($webHookTransfer, $slackMessage, $slackMapping);

        // Assert
        $this->assertEquals($expectedResult, $result);
    }

    public function testRemoveMessageDeletesCorrectPayload(): void
    {
        // Arrange
        $slackMessage = new SlackMessage();
        $slackMessage->setTs('1234567890.123456');

        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock->expects($this->once())
            ->method('toArray')
            ->willReturn(['ok' => true]);
        $this->httpClient->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'https://slack.com/api/chat.delete',
                $this->callback(function (array $options): bool {
                    return $options['json']['channel'] === 'test-slack-channel' &&
                        $options['json']['ts'] === '1234567890.123456';
                })
            )
            ->willReturn($responseMock);

        $slackMapping = new GitHubSlackMapping()
            ->setSlackChannel('test-slack-channel')
            ->setRepository('test-github-repository')
            ->setMentions(['rocket']);

        // Act
        $isRemoved = $this->slackMessenger->removeMessage($slackMessage, $slackMapping);

        // Assert
        $this->assertTrue($isRemoved);
    }
}
