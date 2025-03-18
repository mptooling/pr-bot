<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\SlackMessage;

interface SlackMessageRepositoryInterface
{
    public function findOneByPrNumberAndRepository(int $prNumber, string $repository): ?SlackMessage;

    public function saveSlackMessage(int $prNumber, string $repositoryName, string $slackMessageId): void;
}
