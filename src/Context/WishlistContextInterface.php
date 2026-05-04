<?php

declare(strict_types=1);

namespace Malina141\SyliusWishlistPlugin\Context;

use Malina141\SyliusWishlistPlugin\Entity\WishlistInterface;

interface WishlistContextInterface
{
    public function getWishlist(): WishlistInterface;
}
