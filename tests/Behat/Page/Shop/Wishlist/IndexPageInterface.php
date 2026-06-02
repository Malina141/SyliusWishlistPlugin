<?php

declare(strict_types=1);

namespace Tests\Malina141\SyliusWishlistPlugin\Behat\Page\Shop\Wishlist;

use FriendsOfBehat\PageObjectExtension\Page\SymfonyPageInterface;

interface IndexPageInterface extends SymfonyPageInterface
{
    public function hasProduct(string $productName): bool;

    public function getProductPrice(string $productName): string;

    public function countItems(): int;

    public function hasNoResultsMessage(): bool;
}
