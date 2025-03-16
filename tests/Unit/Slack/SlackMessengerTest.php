<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack;

use App\Entity\GitHubSlackMapping;
use App\Entity\SlackMessage;
use App\Slack\SlackApiClient;
use App\Slack\SlackMessenger;
use App\Slack\SlackMessengerInterface;
use App\Slack\SlackResponse;
use App\Transfers\WebHookTransfer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class SlackMessengerTest extends TestCase
{
    private HttpClientInterface|MockObject $slackApiClient;
    private LoggerInterface|MockObject $logger;
    private SlackMessengerInterface $slackMessenger;

    protected function setUp(): void
    {
        $this->slackApiClient = $this->createMock(SlackApiClient::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->slackMessenger = new SlackMessenger(
            $this->slackApiClient,
            $this->logger,
            withReactions: true,
        );
    }

    public function testSendNewMessageSendsCorrectPayload(): void
    {
         // Arrange & Assert
        $ts = '1234567890.123456';

        $this->slackApiClient->expects($this->once())
            ->method('postChatMessage')
            ->willReturn(new SlackResponse($ts));

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
        $this->assertTrue($result->isSuccessful);
        $this->assertEquals($ts, $result->slackMessageId);
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

        $ts = '1234567890.123456';

        $slackMessage = new SlackMessage();
        $slackMessage->setTs($ts);

        $this->slackApiClient->expects($this->once())
            ->method('updateChatMessage')
            ->willReturn(new SlackResponse('1234567890.123456'));

        $this->slackApiClient->expects($this->once())
            ->method('addReaction')
            ->willReturn(new SlackResponse('1234567890.123456'));

        $slackMapping = new GitHubSlackMapping()
            ->setSlackChannel('test-slack-channel')
            ->setRepository('test-github-repository')
            ->setMentions(['rocket']);

        // Act
        $result = $this->slackMessenger->updateMessage($webHookTransfer, $slackMessage, $slackMapping);

        // Assert
        $this->assertTrue($result->isSuccessful);
        $this->assertEquals($ts, $result->slackMessageId);
    }

    public function testRemoveMessageDeletesCorrectPayload(): void
    {
        // Arrange
        $slackMessage = new SlackMessage();
        $slackMessage->setTs('1234567890.123456');

        $this->slackApiClient->expects($this->once())
            ->method('removeSlackMessage')
            ->willReturn(new SlackResponse());

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
