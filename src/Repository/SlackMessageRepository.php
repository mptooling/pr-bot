<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\SlackMessage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SlackMessage>
 */
final class SlackMessageRepository extends ServiceEntityRepository implements SlackMessageRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SlackMessage::class);
    }

    public function findOneByPrNumber(int $prNumber): ?SlackMessage
    {
        return $this->findOneBy(['prNumber' => $prNumber]);
    }
}
