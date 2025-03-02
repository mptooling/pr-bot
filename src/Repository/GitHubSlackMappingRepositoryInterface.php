<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\GitHubSlackMapping;

/**
 * @method GitHubSlackMapping|null find($id, $lockMode = null, $lockVersion = null)
 * @method GitHubSlackMapping|null findOneBy(array $criteria, array $orderBy = null)
 * @method GitHubSlackMapping[]    findAll()
 */
interface GitHubSlackMappingRepositoryInterface
{
    public function findByRepository(string $repository): ?GitHubSlackMapping;
}
