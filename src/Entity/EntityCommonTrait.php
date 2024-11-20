<?php

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

trait EntityCommonTrait
{

    #[ORM\ManyToOne(targetEntity: User::class)]
    private ?User $createdBy = null;

    #[ORM\Column(type: 'datetime')]
    #[Gedmo\Timestampable(on: 'create')]
    private ?DateTime $createdOn = null;

    #[ORM\Column(type: 'datetime')]
    #[Gedmo\Timestampable(on: 'update')]
    private ?DateTime $updatedOn = null;

    #[ORM\Column(type: 'boolean')]
    private bool $deleted = false;

    public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }

    public function getUserDesc(): ?string
    {
        if($user = $this->createdBy) {
            return $user->getUsername();
        }
        return '';
    }

    public function setCreatedBy(?User $createdBy): self
    {
        $this->createdBy = $createdBy;
        return $this;
    }

    public function getCreatedOn(): ?DateTime
    {
        return $this->createdOn;
    }

    public function setCreatedOn(?DateTime $createdOn): self
    {
        $this->createdOn = $createdOn;
        return $this;
    }

    public function getUpdatedOn(): ?DateTime
    {
        return $this->updatedOn;
    }

    public function setUpdatedOn(?DateTime $updatedOn): self
    {
        $this->updatedOn = $updatedOn;
        return $this;
    }

    public function isDeleted(): bool
    {
        return $this->deleted ?? false;
    }

    public function getDeleted(): bool
    {
        return $this->deleted ?? false;
    }

    public function setDeleted(bool $deleted): self
    {
        $this->deleted = $deleted;
        return $this;
    }

}