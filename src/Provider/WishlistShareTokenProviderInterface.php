<?php

declare(strict_types=1);

namespace Malina141\SyliusWishlistPlugin\Provider;

use Malina141\SyliusWishlistPlugin\Entity\WishlistInterface;

interface WishlistShareTokenProviderInterface
{
    public function provideShareToken(WishlistInterface $wishlist): string;
}
