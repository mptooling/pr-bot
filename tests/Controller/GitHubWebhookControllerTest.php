<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class GitHubWebhookControllerTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
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
}
