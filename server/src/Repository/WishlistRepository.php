<?php

namespace App\Repository;

use App\Entity\Wishlist;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Wishlist>
 */
class WishlistRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Wishlist::class);
    }

    /**
     * Get the top 3 wishlists by total value of items.
     */
    public function findTopWishlistsByValue(): array
    {
        return $this->createQueryBuilder('w')
            ->select('w, SUM(i.price) AS totalValue')
            ->leftJoin('w.items', 'i') // Corrected Join (No "Join::WITH" needed)
            ->groupBy('w.id') // Group by wishlist
            ->orderBy('totalValue', 'DESC') // Sort by highest total value
            ->setMaxResults(3) // Limit to top 3
            ->getQuery()
            ->getResult();
    }

    public function findByUuid(string $uuid): ?Wishlist {
        return $this->findOneBy(['uuid' => $uuid]);
    }

    //    /**
    //     * @return Wishlist[] Returns an array of Wishlist objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('w')
    //            ->andWhere('w.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('w.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Wishlist
    //    {
    //        return $this->createQueryBuilder('w')
    //            ->andWhere('w.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
