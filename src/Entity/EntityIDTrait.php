<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

trait EntityIDTrait
{

    #[ORM\Id]
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private ?int $id = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function isNew(): bool
    {
        return !$this->getId();
    }
}
