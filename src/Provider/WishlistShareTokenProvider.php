<?php

declare(strict_types=1);

namespace Malina141\SyliusWishlistPlugin\Provider;

use Malina141\SyliusWishlistPlugin\Entity\WishlistInterface;
use Sylius\Resource\Generator\RandomnessGeneratorInterface;

final readonly class WishlistShareTokenProvider implements WishlistShareTokenProviderInterface
{
    public function __construct(
        private RandomnessGeneratorInterface $randomnessGenerator,
        private int $tokenLength = 32,
    ) {
    }

    public function provideShareToken(WishlistInterface $wishlist): string
    {
        $shareToken = $wishlist->getShareToken();
        if (null !== $shareToken) {
            return $shareToken;
        }

        $shareToken = $this->randomnessGenerator->generateUriSafeString($this->tokenLength);
        $wishlist->setShareToken($shareToken);

        return $shareToken;
    }
}
