<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "slack_messages")]
class SlackMessage
{
    public function __construct(
        #[ORM\Id]
        #[ORM\Column(type: "integer")]
        private int $prNumber,
        #[ORM\Column(type: "string", length: 255)]
        private string $ts,
        #[ORM\Id]
        #[ORM\Column(type: "string", length: 255)]
        private string $ghRepository,
    ) {
    }

    public function getPrNumber(): int
    {
        return $this->prNumber;
    }

    public function setPrNumber(int $prNumber): self
    {
        $this->prNumber = $prNumber;

        return $this;
    }

    public function getTs(): string
    {
        return $this->ts;
    }

    public function setTs(string $ts): self
    {
        $this->ts = $ts;

        return $this;
    }

    public function getGhRepository(): string
    {
        return $this->ghRepository;
    }

    public function setGhRepository(string $ghRepository): self
    {
        $this->ghRepository = $ghRepository;

        return $this;
    }
}
