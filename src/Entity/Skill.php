<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Persistence\Event\LifecycleEventArgs;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SkillRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Skill
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\OneToMany(targetEntity="OnTask", mappedBy="skill")
     */
    private $inTask;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Level", mappedBy="skill", cascade={"persist", "remove"})
     */
    private $level;

    public function __construct()
    {
        $this->inTask = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getLevel(): ?Level
    {
        return $this->level;
    }

    public function setLevel(Level $level): self
    {
        $this->level = $level;

        // set the owning side of the relation if necessary
        if ($level->getSkill() !== $this) {
            $level->setSkill($this);
        }

        return $this;
    }

    /**
     * @ORM\PostPersist
     */
    public function createLevel(LifecycleEventArgs $args)
    {
        $em = $args->getEntityManager();

        $skill = $em->getRepository(Skill::class)->find($this->id);

        $level = new Level();
        $level->setLevel(1);
        $level->setExpirience(0);
        $level->setSkill($skill);

        $skill->setLevel($level);
        $em->persist($level);

        $em->flush();
    }

    /**
     * @return Collection|OnTask[]
     */
    public function getInTask(): Collection
    {
        return $this->inTask;
    }

    public function addInTask(OnTask $inTask): self
    {
        if (!$this->inTask->contains($inTask)) {
            $this->inTask[] = $inTask;
            $inTask->setSkill($this);
        }

        return $this;
    }

    public function removeInTask(OnTask $inTask): self
    {
        if ($this->inTask->contains($inTask)) {
            $this->inTask->removeElement($inTask);
            // set the owning side to null (unless already changed)
            if ($inTask->getSkill() === $this) {
                $inTask->setSkill(null);
            }
        }

        return $this;
    }
}
