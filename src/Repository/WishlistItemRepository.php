<?php

declare(strict_types=1);

namespace Malina141\SyliusWishlistPlugin\Repository;

use Doctrine\ORM\QueryBuilder;
use Malina141\SyliusWishlistPlugin\Entity\Wishlist;
use Malina141\SyliusWishlistPlugin\Entity\WishlistInterface;
use Malina141\SyliusWishlistPlugin\Entity\WishlistItemInterface;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;

class WishlistItemRepository extends EntityRepository implements WishlistItemRepositoryInterface
{
    public function createShopWishlistItemQueryBuilder(Wishlist $wishlist): QueryBuilder
    {
        $qb = $this->createQueryBuilder('wi');

        if (null === $wishlist->getId()) {
            return $qb->where('1 = 0');
        }

        return $qb
            ->andWhere('wi.wishlist = :wishlist')
            ->setParameter('wishlist', $wishlist)
        ;
    }

    public function findOneByIdAndWishlist(string|int $id, WishlistInterface $wishlist): ?WishlistItemInterface
    {
        /** @var WishlistItemInterface|null $item */
        $item = $this->createQueryBuilder('wi')
            ->andWhere('wi.id = :id')
            ->andWhere('wi.wishlist = :wishlist')
            ->setParameter('id', $id)
            ->setParameter('wishlist', $wishlist)
            ->getQuery()
            ->getOneOrNullResult()
        ;

        return $item;
    }

    /**
     * @param array<string|int> $ids
     */
    public function findByIdsAndWishlist(array $ids, WishlistInterface $wishlist): array
    {
        /** @var array<WishlistItemInterface> $items */
        $items = $this->createQueryBuilder('wi')
            ->andWhere('wi.id IN (:ids)')
            ->andWhere('wi.wishlist = :wishlist')
            ->setParameter('ids', $ids)
            ->setParameter('wishlist', $wishlist)
            ->getQuery()
            ->getResult()
        ;

        return $items;
    }
}
