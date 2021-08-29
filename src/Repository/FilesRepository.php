<?php

namespace App\Repository;

use App\Entity\Files;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Files|null find($id, $lockMode = null, $lockVersion = null)
 * @method Files|null findOneBy(array $criteria, array $orderBy = null)
 * @method Files[]    findAll()
 * @method Files[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @method Files[]    findByFolder(string $path)
 */
class FilesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Files::class);
    }

    /**
     * @return Files[] Returns an array of Files objects
     */
    public function findAllFolder($value)
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.path = :val')
            ->setParameter('val', $value)
            ->orderBy('f.type', 'DESC')
            ->addOrderBy('f.name', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return int
     */
    public function deleteFolder($path, $hash_delete, $permanently = false): int
    {
        return $this->createQueryBuilder('f')
            ->update()
            ->set('f.status', $permanently ? '3' : '2')
            ->set('f.hash_delete', ':val1')
            ->where('f.path LIKE :val')
            ->andWhere('f.hash_delete is NULL')
            ->setParameter('val', $path.'%')
            ->setParameter('val1', $hash_delete)
            ->getQuery()
            ->execute()
        ;
    }

    /**
     * @return Files[]
     */
    public function restoreFolder($path, $hash_delete)
    {
        $this->createQueryBuilder('f')
            ->update()
            ->set('f.status', '1')
            ->where('f.path LIKE :val')
            ->andWhere('f.hash_delete = :val1')
            ->setParameter('val', $path.'%')
            ->setParameter('val1', $hash_delete)
            ->getQuery()
            ->execute()
        ;

        return $this->createQueryBuilder('f')
            ->where('f.hash_delete = :val')
            ->setParameter('val', $hash_delete)
            ->getQuery()
            ->getResult()
        ;
    }

    /*
    public function findOneBySomeField($value): ?Files
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
