<?php

declare(strict_types=1);

namespace Tests\Malina141\SyliusWishlistPlugin\Behat\Element\Shop;

interface WishlistWidgetElementInterface
{
    public function follow(): void;

    public function getWishlistCount(): int;
}
