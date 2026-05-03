<?php

declare(strict_types=1);

namespace Malina141\SyliusWishlistPlugin\Context;

use Malina141\SyliusWishlistPlugin\Entity\WishlistInterface;
use Malina141\SyliusWishlistPlugin\Exception\WishlistNotFoundException;

final readonly class CompositeWishlistContext implements WishlistContextInterface
{
    public function __construct(
        private iterable $wishlistContexts
    )
    {
    }

    public function getWishlist(): WishlistInterface
    {
        foreach ($this->wishlistContexts as $wishlistContext) {
            try {
                return $wishlistContext->getWishlist();
            } catch (WishlistNotFoundException $exception) {
                continue;
            }
        }

        throw new WishlistNotFoundException('No wishlist could be provided');
    }
}
