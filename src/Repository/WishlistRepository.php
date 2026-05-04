<?php

declare(strict_types=1);

namespace Malina141\SyliusWishlistPlugin\Repository;

use Malina141\SyliusWishlistPlugin\Entity\WishlistInterface;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;
use Sylius\Component\Channel\Model\ChannelInterface;
use Sylius\Component\Core\Model\ShopUserInterface;

class WishlistRepository extends EntityRepository implements WishlistRepositoryInterface
{
    public function findOneByOwnerAndChannel(ShopUserInterface $owner, ChannelInterface $channel): ?WishlistInterface
    {
        /** @var WishlistInterface|null $wishlist */
        $wishlist = $this->createQueryBuilder('w')
            ->andWhere('w.owner = :owner')
            ->andWhere('w.channel = :channel')
            ->setParameter('owner', $owner)
            ->setParameter('channel', $channel)
            ->getQuery()
            ->getOneOrNullResult()
        ;

        return $wishlist;
    }
}
