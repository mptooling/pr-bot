<?php

declare(strict_types=1);

namespace App\Tests\Unit\Authenticator;

use App\Authenticator\SignatureAuthenticator;
use PHPUnit\Framework\TestCase;

class SignatureAuthenticatorTest extends TestCase
{
    private string $secret;
    protected function setUp(): void
    {
        $this->secret = $_ENV['GITHUB_WEBHOOK_SECRET'];
    }
    public function testIsAuthenticated(): void
    {
        // Arrange
        $payload = [
            "action" => "closed",
            "pull_request" => [
                "number" => 42,
                "html_url" => "https://github.com/example/repo/pull/42",
                "user" => ["login" => "testuser"],
            ],
        ];
        $payloadJson = (string) json_encode($payload);
        $signature = 'sha256=' . hash_hmac('sha256', $payloadJson, $this->secret);

        // Act
        $isAuthenticated = new SignatureAuthenticator($this->secret)->isAuthenticated($payloadJson, $signature);

        //Assert
        $this->assertTrue($isAuthenticated);
    }
}
