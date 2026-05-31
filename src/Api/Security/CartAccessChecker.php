<?php

declare(strict_types=1);

namespace Malina141\SyliusWishlistPlugin\Api\Security;

use Sylius\Bundle\ApiBundle\Context\UserContextInterface;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\ShopUserInterface;

final readonly class CartAccessChecker implements CartAccessCheckerInterface
{
    public function __construct(
        private UserContextInterface $userContext,
    ) {
    }

    public function canAccess(OrderInterface $cart): bool
    {
        $cartCustomer = $cart->getCustomer();
        if (!$cartCustomer instanceof CustomerInterface || null === $cartCustomer->getUser()) {
            return true;
        }

        $currentUser = $this->userContext->getUser();

        return $currentUser instanceof ShopUserInterface && $currentUser->getCustomer() === $cartCustomer;
    }
}
