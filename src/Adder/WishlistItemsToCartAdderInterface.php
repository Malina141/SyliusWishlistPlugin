<?php

declare(strict_types=1);

namespace Malina141\SyliusWishlistPlugin\Adder;

use Malina141\SyliusWishlistPlugin\Entity\WishlistItemInterface;
use Sylius\Component\Core\Model\OrderInterface;

interface WishlistItemsToCartAdderInterface
{
    /** @param WishlistItemInterface[] $wishlistItems */
    public function add(array $wishlistItems, OrderInterface $cart): void;
}
