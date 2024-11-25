<?php

namespace App\Entity;

use App\Repository\GameSettingRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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

    #[ORM\Column(unique: true)]
    private ?int $level;

    #[ORM\Column]
    private ?bool $isActive = false;

    #[ORM\OneToMany(targetEntity: GameSession::class, mappedBy: 'gameSetting', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private iterable  $gameSessions;

    use EntityCommonTrait;

    public function __construct()
    {
        $this->gameSessions = new ArrayCollection();
    }

    public function __toString(): string
    {
        return "$this->id, $this->totalSecond, $this->level";
    }


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

    /**
     * @return Collection<int, GameSession>
     */
    public function getGameSessions(): Collection
    {
        return $this->gameSessions;
    }

    public function addGameSession(GameSession $gameSession): static
    {
        if (!$this->gameSessions->contains($gameSession)) {
            $this->gameSessions->add($gameSession);
            $gameSession->setGameSetting($this);
        }

        return $this;
    }

    public function removeGameSession(GameSession $gameSession): static
    {
        if ($this->gameSessions->removeElement($gameSession)) {
            // set the owning side to null (unless already changed)
            if ($gameSession->getGameSetting() === $this) {
                $gameSession->setGameSetting(null);
            }
        }

        return $this;
    }

    public function getLevel(): ?int
    {
        return $this->level;
    }

    public function setLevel(?int $level): self
    {
        $this->level = $level;
        return $this;
    }

    public function getIsActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(?bool $isActive): self
    {
        $this->isActive = $isActive;
        return $this;
    }

}
