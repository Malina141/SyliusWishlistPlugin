<?php

declare(strict_types=1);

namespace Malina141\SyliusWishlistPlugin\Merger;

use Sylius\Component\Core\Model\ShopUserInterface;

interface GuestWishlistLoginMergerInterface
{
    public function merge(string $token, ShopUserInterface $user): void;
}
