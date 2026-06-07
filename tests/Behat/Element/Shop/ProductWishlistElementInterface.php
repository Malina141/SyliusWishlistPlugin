<?php

declare(strict_types=1);

namespace Tests\Malina141\SyliusWishlistPlugin\Behat\Element\Shop;

interface ProductWishlistElementInterface
{
    public function addCurrentProduct(): void;

    public function removeCurrentProduct(): void;

    public function addProductFromList(string $productCode): void;

    public function removeProductFromList(string $productCode): void;

    public function hasCurrentProductRemoveButton(): bool;
}
