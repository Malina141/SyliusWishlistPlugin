<?php

declare(strict_types=1);

namespace Tests\Malina141\SyliusWishlistPlugin\Behat\Page\Admin\Wishlist;

use FriendsOfBehat\PageObjectExtension\Page\SymfonyPageInterface;

interface ShowPageInterface extends SymfonyPageInterface
{
    public function hasWishlistName(string $name): bool;

    public function hasOwner(string $owner): bool;

    public function hasChannel(string $channelName): bool;

    public function hasProduct(string $productName): bool;

    public function hasVariantCode(string $variantCode): bool;

    public function hasNoItemsMessage(): bool;
}
