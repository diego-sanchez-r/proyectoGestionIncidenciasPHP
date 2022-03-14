<?php

namespace App\Repository;

use App\Entity\Incidencias;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Clientes;

/**
 * @method Incidencias|null find($id, $lockMode = null, $lockVersion = null)
 * @method Incidencias|null findOneBy(array $criteria, array $orderBy = null)
 * @method Incidencias[]    findAll()
 * @method Incidencias[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class IncidenciasRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Incidencias::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(Incidencias $entity, bool $flush = true): void
    {
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function remove(Incidencias $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    // /**
    //  * @return Incidencias[] Returns an array of Incidencias objects
    //  */
    
    public function findByIdCliente($id)
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.cliente = :val')
            ->setParameter('val', $id)
            ->getQuery()
            ->getResult()
        ;
    }
    

    /*
    public function findOneBySomeField($value): ?Incidencias
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
