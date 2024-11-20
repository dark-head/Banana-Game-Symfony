<?php

namespace App\Entity;

use App\Repository\GameSettingRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'game_setting')]
#[ORM\Entity(repositoryClass: GameSettingRepository::class)]
class GameSetting
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $totalSecond = 30;

    #[ORM\Column(type: 'string', length: 10)]
    private ?string $difficulty;

    use EntityCommonTrait;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTotalSecond(): ?int
    {
        return $this->totalSecond;
    }

    public function setTotalSecond(int $totalSecond): static
    {
        $this->totalSecond = $totalSecond;

        return $this;
    }

    // Getter and Setter for difficulty
    public function getDifficulty(): ?string
    {
        return $this->difficulty;
    }

    public function setDifficulty(string $difficulty): self
    {
        $this->difficulty = $difficulty;
        return $this;
    }
}
