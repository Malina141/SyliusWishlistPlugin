<?php

declare(strict_types=1);

namespace Malina141\SyliusWishlistPlugin\Repository;

use Malina141\SyliusWishlistPlugin\Entity\WishlistInterface;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;
use Sylius\Component\Core\Model\ShopUserInterface;

class WishlistRepository extends EntityRepository implements WishlistRepositoryInterface
{
    public function findOneByOwner(ShopUserInterface $owner): ?WishlistInterface
    {
        /** @var WishlistInterface|null $wishlist */
        $wishlist =  $this->createQueryBuilder('w')
            ->andWhere('w.owner = :owner')
            ->setParameter('owner', $owner)
            ->getQuery()
            ->getOneOrNullResult()
            ;

        return $wishlist;
    }
}
