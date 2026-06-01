<?php

declare(strict_types=1);

namespace Malina141\SyliusWishlistPlugin\Context;

use Malina141\SyliusWishlistPlugin\Entity\WishlistInterface;
use Symfony\Contracts\Service\ResetInterface;

final class CachedWishlistContext implements WishlistContextInterface, ResetInterface
{
    private ?WishlistInterface $wishlist = null;

    public function __construct(
        private readonly WishlistContextInterface $wishlistContext,
    ) {
    }

    public function getWishlist(): WishlistInterface
    {
        if (null === $this->wishlist) {
            $this->wishlist = $this->wishlistContext->getWishlist();
        }

        return $this->wishlist;
    }

    public function reset(): void
    {
        $this->wishlist = null;
    }
}
