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
    private ManagerRegistry $managerRegistry;

    public function __construct(ManagerRegistry $registry, ManagerRegistry $managerRegistry)
    {
        parent::__construct($registry, SlackMessage::class);
        $this->managerRegistry = $managerRegistry;
    }

    public function findOneByPrNumberAndRepository(int $prNumber, string $repository): ?SlackMessage
    {
        return $this->findOneBy([
            'prNumber' => $prNumber,
            'ghRepository' => $repository,
        ]);
    }

    public function saveSlackMessage(int $prNumber, string $repositoryName, string $slackMessageId): void
    {
        $entity = new SlackMessage();
        $entity->setPrNumber($prNumber)
            ->setGhRepository($repositoryName)
            ->setTs($slackMessageId);

        $this->managerRegistry->getManager()->persist($entity);
        $this->managerRegistry->getManager()->flush();
    }
}
