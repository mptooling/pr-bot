<?php

declare(strict_types=1);

namespace App\Validator;

final readonly class Authenticator
{
    public function __construct(private string $secret)
    {
    }

    public function isAuthenticated(string $payload, string $signature): bool
    {
        $expected = "sha256=" . hash_hmac('sha256', $payload, $this->secret);

        return hash_equals($expected, $signature);
    }
}
