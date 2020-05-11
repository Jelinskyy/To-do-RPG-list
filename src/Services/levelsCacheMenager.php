<?php

namespace App\Services;

use Symfony\Contracts\Cache\CacheInterface;
use App\Repository\SkillRepository;
use Symfony\Contracts\Cache\ItemInterface;
use App\Entity\Skill;

Class levelsCacheMenager
{
    /**
     * @var CacheInterface
     */
    private $cacheShort;

    /**
     * @var SkillRepository
     */
    private $skillRepository;


    public function __construct(CacheInterface $cacheShort, SkillRepository $skillRepository)
    {
        $this->skillRepository=$skillRepository;
        $this->cacheShort=$cacheShort;
    }

    public function getLevel(string $name){
        $item = $this->cacheShort->getItem("level.next.{$name}");
        if(!$item->isHit())
        {
            $this->updateLevel($name, $item);
        }
        return $item->get();
    }

    public function updateLevel(string $name,ItemInterface $item)
    {
        $skill = $this->skillRepository->findOneBy(['name'=>$name]);
        $level = $skill->getLevel()->getLevel();

        $item->set($this->calcLevel($level));
        $this->cacheShort->save($item);
    }

    public function calcLevel(int $level)
    {
        return round(20*pow(1.2 , $level-1), 2);
    }
}