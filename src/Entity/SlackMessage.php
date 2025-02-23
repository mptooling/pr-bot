<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "slack_messages")]
final class SlackMessage
{
    #[ORM\Id]
    #[ORM\Column(type: "integer")]
    private ?int $prNumber = null;

    #[ORM\Column(type: "string", length: 255)]
    private ?string $ts = null;

    public function getPrNumber(): ?int
    {
        return $this->prNumber;
    }

    public function setPrNumber(?int $prNumber): self
    {
        $this->prNumber = $prNumber;

        return $this;
    }

    public function getTs(): ?string
    {
        return $this->ts;
    }

    public function setTs(?string $ts): self
    {
        $this->ts = $ts;

        return $this;
    }
}
