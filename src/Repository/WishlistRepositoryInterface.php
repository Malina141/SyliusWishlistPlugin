<?php

declare(strict_types=1);

namespace Malina141\SyliusWishlistPlugin\Repository;

use Malina141\SyliusWishlistPlugin\Entity\WishlistInterface;
use Sylius\Component\Channel\Model\ChannelInterface;
use Sylius\Component\Core\Model\ShopUserInterface;
use Sylius\Resource\Doctrine\Persistence\RepositoryInterface;

/**
 * @extends RepositoryInterface<WishlistInterface>
 */
interface WishlistRepositoryInterface extends RepositoryInterface
{
    public function findOneByOwnerAndChannel(ShopUserInterface $owner, ChannelInterface $channel): ?WishlistInterface;

    public function findOneByTokenAndChannel(string $token, ChannelInterface $channel): ?WishlistInterface;
}
