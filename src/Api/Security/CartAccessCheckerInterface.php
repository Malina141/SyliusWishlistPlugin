<?php

declare(strict_types=1);

namespace Malina141\SyliusWishlistPlugin\Api\Security;

use Sylius\Component\Core\Model\OrderInterface;

interface CartAccessCheckerInterface
{
    public function canAccess(OrderInterface $cart): bool;
}
