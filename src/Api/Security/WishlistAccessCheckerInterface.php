<?php

declare(strict_types=1);

namespace Malina141\SyliusWishlistPlugin\Api\Security;

use Malina141\SyliusWishlistPlugin\Entity\WishlistInterface;

interface WishlistAccessCheckerInterface
{
    public function canAccessPrivateToken(WishlistInterface $wishlist): bool;
}
