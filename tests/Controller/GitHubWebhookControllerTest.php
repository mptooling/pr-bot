<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\SlackMessage;
use App\Repository\SlackMessageRepositoryInterface;
use App\Slack\SlackMessengerInterface;
use App\Transfers\WebHookTransfer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class GitHubWebhookControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private string $githubWebhookSecret;

    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->githubWebhookSecret = $_ENV['GITHUB_WEBHOOK_SECRET'];
        $this->entityManager = self::getContainer()->get(EntityManagerInterface::class);
    }

    public function testHandleWebhookForbidsUnsignedRequest(): void
    {
        // Arrange
        $payload = ['action' => 'opened', 'pull_request' => ['number' => 42]];

        // Act
        $this->client->request(
            method: 'POST',
            uri: '/webhook/github',
            server: ['HTTP_CONTENT_TYPE' => 'application/json'],
            content: json_encode($payload), // @phpstan-ignore-line
        );

        // Assert
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testHandleWebhookForbidsInvalidSignature(): void
    {
        // Arrange
        $payload = ['action' => 'opened', 'pull_request' => ['number' => 42]];

        // Act
        $this->client->request(
            method: 'POST',
            uri: '/webhook/github',
            server: ['HTTP_CONTENT_TYPE' => 'application/json', 'HTTP_X-Hub-Signature-256' => 'sha256=invalid'],
            content: json_encode($payload), // @phpstan-ignore-line
        );

        // Assert
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testHandleWebhookPROpened(): void
    {
        // Arrange
        $slackMessengerMock = $this->createMock(SlackMessengerInterface::class);
        $slackMessengerMock->expects($this->once())
            ->method('sendNewMessage')
            ->with(new WebHookTransfer(42, 'https://github.com/example/repo/pull/42', 'testuser'))
            ->willReturn(['ts' => '1234567890']);

        self::getContainer()->set(SlackMessengerInterface::class, $slackMessengerMock);

        $payload = [
            "action" => "opened",
            "pull_request" => [
                "number" => 42,
                "html_url" => "https://github.com/example/repo/pull/42",
                "user" => ["login" => "testuser"],
            ],
        ];
        $payloadJson = (string) json_encode($payload);
        $correctSignature = 'sha256=' . hash_hmac('sha256', $payloadJson, $this->githubWebhookSecret);

        // Act
        $this->client->request(
            method: 'POST',
            uri: '/webhook/github',
            server: ['HTTP_CONTENT_TYPE' => 'application/json', 'HTTP_X-Hub-Signature-256' => $correctSignature],
            content: json_encode($payload), // @phpstan-ignore-line
        );

        // Assert HTTP response is successful
        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        // Check if SlackMessage was stored in the database
        $slackMessage = $this->entityManager->getRepository(SlackMessage::class)->find(42);
        $this->assertNotNull($slackMessage, "Slack message should be stored.");
        $this->assertEquals(42, $slackMessage->getPrNumber());
        $this->assertNotEmpty($slackMessage->getTs(), "Slack timestamp should not be empty.");
    }

    public function testHandleWebhookPrClosed(): void
    {
        // Arrange
        $newSlackMessageTimestamp = '1234567891';
        $slackMessengerMock = $this->createMock(SlackMessengerInterface::class);
        $slackMessengerMock->expects($this->once())
            ->method('sendNewMessage')
            ->with(new WebHookTransfer(42, 'https://github.com/example/repo/pull/42', 'testuser'))
            ->willReturn(['ts' => $newSlackMessageTimestamp]);
        self::getContainer()->set(SlackMessengerInterface::class, $slackMessengerMock);

        $repository = $this->createMock(SlackMessageRepositoryInterface::class);
        $repository->expects($this->once())
            ->method('findOneByPrNumber')
            ->with(42)
            ->willReturn((new SlackMessage()->setPrNumber(42)->setTs('1234567890')));
        self::getContainer()->set(SlackMessageRepositoryInterface::class, $repository);

        $payload = [
            "action" => "closed",
            "pull_request" => [
                "number" => 42,
                "html_url" => "https://github.com/example/repo/pull/42",
                "user" => ["login" => "testuser"],
            ],
        ];
        $payloadJson = (string) json_encode($payload);
        $correctSignature = 'sha256=' . hash_hmac('sha256', $payloadJson, $this->githubWebhookSecret);

        // Act
        $this->client->request(
            method: 'POST',
            uri: '/webhook/github',
            server: ['HTTP_CONTENT_TYPE' => 'application/json', 'HTTP_X-Hub-Signature-256' => $correctSignature],
            content: json_encode($payload), // @phpstan-ignore-line
        );

        // Assert HTTP response is successful
        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        // Check if SlackMessage was stored in the database
        $slackMessage = $this->entityManager->getRepository(SlackMessage::class)->find(42);
        $this->assertNotNull($slackMessage, "Slack message should be stored.");
        $this->assertEquals(42, $slackMessage->getPrNumber());
        $this->assertEquals($newSlackMessageTimestamp, $slackMessage->getTs(), 'Slack timestamp should be updated.');
    }
}
