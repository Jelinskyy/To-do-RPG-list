<?php

namespace App\Repository;

use App\Entity\OnTask;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method OnTask|null find($id, $lockMode = null, $lockVersion = null)
 * @method OnTask|null findOneBy(array $criteria, array $orderBy = null)
 * @method OnTask[]    findAll()
 * @method OnTask[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OnTaskRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OnTask::class);
    }

    /**
     * @return OnTask[] Returns an array of OnTask objects
     */

    public function findByAssigment($assigId)
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.assigment = :val')
            ->setParameter('val', $assigId)
            ->getQuery()
            ->getResult()
        ;
    }

    /*
    public function findOneBySomeField($value): ?OnTask
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
