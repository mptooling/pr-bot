<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\SlackMessage;

interface SlackMessageRepositoryInterface
{
    public function findOneByPrNumber(int $prNumber): ?SlackMessage;
}
