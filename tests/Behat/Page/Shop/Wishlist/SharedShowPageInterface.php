<?php

declare(strict_types=1);

namespace Tests\Malina141\SyliusWishlistPlugin\Behat\Page\Shop\Wishlist;

use Sylius\Behat\Page\SyliusPageInterface;

interface SharedShowPageInterface extends SyliusPageInterface
{
    public function hasSharedWishlistPage(): bool;

    public function hasProduct(string $productName): bool;

    public function getProductPrice(string $productName): string;

    public function isAccessDenied(): bool;
}
