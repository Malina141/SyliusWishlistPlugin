<?php

declare(strict_types=1);

namespace Malina141\SyliusWishlistPlugin\Merger;

use Malina141\SyliusWishlistPlugin\Entity\WishlistInterface;
use Sylius\Component\Core\Model\ShopUserInterface;

interface WishlistMergerInterface
{
    public function merge(ShopUserInterface $user, WishlistInterface $guestWishlist): void;
}
