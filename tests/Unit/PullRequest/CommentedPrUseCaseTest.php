<?php

declare(strict_types=1);

namespace App\Tests\Unit\PullRequest;

use App\Entity\GitHubSlackMapping;
use App\Entity\SlackMessage;
use App\PullRequest\CommentedPrUseCase;
use App\Repository\GitHubSlackMappingRepositoryInterface;
use App\Repository\SlackMessageRepositoryInterface;
use App\Slack\SlackApiClient;
use App\Slack\SlackResponse;
use App\Transfers\WebHookTransfer;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class CommentedPrUseCaseTest extends TestCase
{
    private SlackMessageRepositoryInterface $slackMessageRepository;

    private GitHubSlackMappingRepositoryInterface $gitHubSlackMappingRepository;

    private SlackApiClient $slackApiClient;

    private CommentedPrUseCase $useCase;

    protected function setUp(): void
    {
        $this->slackMessageRepository = $this->createMock(SlackMessageRepositoryInterface::class);
        $this->slackApiClient = $this->createMock(SlackApiClient::class);
        $this->gitHubSlackMappingRepository = $this->createMock(GitHubSlackMappingRepositoryInterface::class);
        $this->useCase = new CommentedPrUseCase(
            $this->slackMessageRepository,
            $this->gitHubSlackMappingRepository,
            $this->slackApiClient,
            $this->createMock(LoggerInterface::class)
        );
    }

    public function testHandlePrCommentedFirstTime(): void
    {
        // Arrange & Assert
        $webHookTransfer = new WebHookTransfer(
            repository: 'example/repo',
            prNumber: 42,
            prTitle: 'The title',
            prUrl: 'https://github.com/example/repo/pull/42',
            prAuthor: 'testuser'
        );

        $this->slackMessageRepository->expects($this->once())
            ->method('findOneByPrNumberAndRepository')
            ->with(42, 'example/repo')
            ->willReturn(new SlackMessage());

        $gitHubSlackMapping = new GitHubSlackMapping()
            ->setSlackChannel('test-slack-channel')
            ->setRepository('test-github-repository')
            ->setMentions(['<!subtram^S12345678>']);

        $this->gitHubSlackMappingRepository->expects($this->once())
            ->method('findByRepository')
            ->willReturn($gitHubSlackMapping);


        $this->slackApiClient->expects($this->once())
            ->method('getBotUserId')
            ->willReturn('123');

        $this->slackApiClient->expects($this->once())
            ->method('getMessageReactions')
            ->willReturn(new SlackResponse());

        $this->slackApiClient->expects($this->once())
            ->method('addReaction')
            ->willReturn(new SlackResponse());

        // Act
        $this->useCase->handle($webHookTransfer);
    }

    public function testHandlePrAlreadyCommented(): void
    {
        // Arrange & Assert
        $webHookTransfer = new WebHookTransfer(
            repository: 'example/repo',
            prNumber: 42,
            prTitle: 'The title',
            prUrl: 'https://github.com/example/repo/pull/42',
            prAuthor: 'testuser'
        );

        $this->slackMessageRepository->expects($this->once())
            ->method('findOneByPrNumberAndRepository')
            ->with(42, 'example/repo')
            ->willReturn(new SlackMessage());

        $gitHubSlackMapping = new GitHubSlackMapping()
            ->setSlackChannel('test-slack-channel')
            ->setRepository('test-github-repository')
            ->setMentions(['<!subtram^S12345678>']);

        $this->gitHubSlackMappingRepository->expects($this->once())
            ->method('findByRepository')
            ->willReturn($gitHubSlackMapping);

        $slackReactionsResponse = new SlackResponse();
        $slackReactionsResponse->data->add([
            'reactions' => [
                [
                    'name' => 'speech_balloon',
                    'users' => ["123"],
                    'count' => 1,
                ],
            ]
        ]);

        $this->slackApiClient->expects($this->once())
            ->method('getBotUserId')
            ->willReturn('123');

        $this->slackApiClient->expects($this->once())
            ->method('getMessageReactions')
            ->willReturn($slackReactionsResponse);

        $this->slackApiClient->expects($this->never())
            ->method('addReaction');

        // Act
        $this->useCase->handle($webHookTransfer);
    }
}
