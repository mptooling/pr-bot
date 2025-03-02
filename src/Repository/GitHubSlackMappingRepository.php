<?php

namespace App\Repository;

use App\Entity\GitHubSlackMapping;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<GitHubSlackMapping>
 */
class GitHubSlackMappingRepository extends ServiceEntityRepository implements GitHubSlackMappingRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GitHubSlackMapping::class);
    }

    public function findByRepository(string $repository): ?GitHubSlackMapping
    {
        return $this->findOneBy(['repository' => $repository]);
    }
}
