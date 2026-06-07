<?php

declare(strict_types=1);

namespace Tests\Malina141\SyliusWishlistPlugin\Behat\Page\Shop\Wishlist;

use Sylius\Behat\Page\SyliusPageInterface;

interface IndexPageInterface extends SyliusPageInterface
{
    public function hasProduct(string $productName): bool;

    public function getProductPrice(string $productName): string;

    /** @return list<string> */
    public function getProductOptionTexts(string $productName): array;

    public function removeProduct(string $productName): void;

    public function selectProduct(string $productName): void;

    public function selectAllProducts(): void;

    public function bulkDeleteSelectedProducts(): void;

    public function bulkAddSelectedProductsToCart(): void;

    public function countItems(): int;

    public function hasNoResultsMessage(): bool;

    public function getWishlistName(): string;

    public function renameWishlist(string $name): void;

    public function hasNameSavedMessage(): bool;

    public function reload(): void;

    public function shareWishlist(): void;

    public function unshareWishlist(): void;

    public function isWishlistMarkedAsShared(): bool;

    public function hasPublicWishlistLink(): bool;

    public function copyPublicWishlistLink(): void;

    public function getPublicWishlistLink(): string;
}
