<?php

namespace App\Entity;

use App\Repository\StatusRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=StatusRepository::class)
 */
class Status
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id_status;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name_status;

    public function getId(): ?int
    {
        return $this->id_status;
    }

    public function getNameStatus(): ?string
    {
        return $this->name_status;
    }

    public function setNameStatus(string $name_status): self
    {
        $this->name_status = $name_status;

        return $this;
    }
}
