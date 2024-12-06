<?php

namespace App\Entity;

use App\Repository\GameSessionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'game_session')]
#[ORM\Entity(repositoryClass: GameSessionRepository::class)]
class GameSession
{
    use EntityIDTrait;

    #[ORM\Column]
    private ?int $score = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $date = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    private ?User $user = null;

    #[ORM\JoinColumn(nullable: true)]
    #[ORM\ManyToOne(targetEntity: GameSetting::class, inversedBy: 'gameSessions')]
    private ?GameSetting $gameSetting;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getScore(): ?int
    {
        return $this->score;
    }

    public function setScore(int $score): static
    {
        $this->score = $score;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getGameSetting(): ?GameSetting
    {
        return $this->gameSetting;
    }

    public function setGameSetting(?GameSetting $gameSetting): self
    {
        $this->gameSetting = $gameSetting;
        return $this;
    }
}
