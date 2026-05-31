<?php

declare(strict_types=1);

namespace Malina141\SyliusWishlistPlugin\Provider;

use Malina141\SyliusWishlistPlugin\Entity\WishlistInterface;
use Malina141\SyliusWishlistPlugin\Generator\WishlistTokenGeneratorInterface;

final readonly class WishlistShareTokenProvider implements WishlistShareTokenProviderInterface
{
    public function __construct(
        private WishlistTokenGeneratorInterface $tokenGenerator,
    ) {
    }

    public function provideShareToken(WishlistInterface $wishlist): string
    {
        $shareToken = $wishlist->getShareToken();
        if (null !== $shareToken) {
            return $shareToken;
        }

        $shareToken = $this->tokenGenerator->generate();
        $wishlist->setShareToken($shareToken);

        return $shareToken;
    }
}
