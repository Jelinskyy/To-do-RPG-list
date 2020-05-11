<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\AssigmentRepository")
 */
class Assigment
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=127)
     */
    private $title;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $description;

    /**
     * @ORM\OneToMany(targetEntity="OnTask", mappedBy="assigment", fetch="EXTRA_LAZY")
     */
    private $forTask;

    public function __construct()
    {
        $this->forTask = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return Collection|OnTask[]
     */
    public function getForTask(): Collection
    {
        return $this->forTask;
    }

    public function addForTask(OnTask $forTask): self
    {
        if (!$this->forTask->contains($forTask)) {
            $this->forTask[] = $forTask;
            $forTask->setAssigment($this);
        }

        return $this;
    }

    public function removeForTask(OnTask $forTask): self
    {
        if ($this->forTask->contains($forTask)) {
            $this->forTask->removeElement($forTask);
            // set the owning side to null (unless already changed)
            if ($forTask->getAssigment() === $this) {
                $forTask->setAssigment(null);
            }
        }

        return $this;
    }
}
