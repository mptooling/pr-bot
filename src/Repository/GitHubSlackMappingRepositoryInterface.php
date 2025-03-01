<?php

declare(strict_types=1);


namespace App\Repository;

use App\Entity\GitHubSlackMapping;

interface GitHubSlackMappingRepositoryInterface
{
    public function findByRepository(string $repository): ?GitHubSlackMapping;
}
