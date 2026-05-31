<?php

declare(strict_types=1);

namespace Malina141\SyliusWishlistPlugin\Api\Security;

use Malina141\SyliusWishlistPlugin\Entity\WishlistInterface;
use Sylius\Bundle\ApiBundle\Context\UserContextInterface;

final readonly class WishlistAccessChecker implements WishlistAccessCheckerInterface
{
    public function __construct(
        private UserContextInterface $userContext,
    ) {
    }

    public function canAccessPrivateToken(WishlistInterface $wishlist): bool
    {
        $owner = $wishlist->getOwner();
        if (null === $owner) {
            return true;
        }

        return $owner === $this->userContext->getUser();
    }
}
