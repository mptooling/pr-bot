<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\GitHubSlackMappingRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GitHubSlackMappingRepository::class)]
#[ORM\Table(name: "github_slack_mapping")]
class GitHubSlackMapping
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\Column(type: "string", length: 255, unique: true)]
    private string $repository;

    #[ORM\Column(type: "string", length: 255)]
    private string $slackChannel;

    #[ORM\Column(type: "json")]
    private array $mentions = [];

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRepository(): string
    {
        return $this->repository;
    }
    public function setRepository(string $repository): self
    {
        $this->repository = $repository;
        return $this;
    }

    public function getSlackChannel(): string
    {
        return $this->slackChannel;
    }
    public function setSlackChannel(string $slackChannel): self
    {
        $this->slackChannel = $slackChannel;
        return $this;
    }

    public function getMentions(): array
    {
        return $this->mentions;
    }
    public function setMentions(array $mentions): self
    {
        $this->mentions = $mentions;
        return $this;
    }
}
