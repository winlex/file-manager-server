<?php

namespace App\Entity;

use App\Repository\TypeRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=TypeRepository::class)
 */
class Type
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id_type;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name_type;

    public function getId(): ?int
    {
        return $this->id_type;
    }

    public function getNameType(): ?string
    {
        return $this->name_type;
    }

    public function setNameType(string $name_type): self
    {
        $this->name_type = $name_type;

        return $this;
    }

    public function getIdType(): ?int
    {
        return $this->id_type;
    }
}
