<?php

namespace App\Repository;

use App\Entity\GitHubSlackMapping;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<GitHubSlackMapping>
 *
 * @method GitHubSlackMapping|null find($id, $lockMode = null, $lockVersion = null)
 * @method GitHubSlackMapping|null findOneBy(array $criteria, array $orderBy = null)
 * @method GitHubSlackMapping[] findAll()
 * @method GitHubSlackMapping[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
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
