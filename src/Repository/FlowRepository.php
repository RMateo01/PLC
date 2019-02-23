<?php

namespace App\Repository;

use App\Entity\Flow;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Flow|null find($id, $lockMode = null, $lockVersion = null)
 * @method Flow|null findOneBy(array $criteria, array $orderBy = null)
 * @method Flow[]    findAll()
 * @method Flow[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FlowRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Flow::class);
    }

//    /**
//     * @return Flow[] Returns an array of Flow objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('f.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Flow
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
