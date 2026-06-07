<?php

declare(strict_types=1);

namespace Tests\Malina141\SyliusWishlistPlugin\Behat\Page\Admin\Wishlist;

use FriendsOfBehat\PageObjectExtension\Page\SymfonyPage;

final class ShowPage extends SymfonyPage implements ShowPageInterface
{
    public function getRouteName(): string
    {
        return 'malina141_sylius_wishlist_admin_wishlist_show';
    }

    public function hasWishlistName(string $name): bool
    {
        return $this->hasText($name);
    }

    public function hasOwner(string $owner): bool
    {
        return $this->hasText($owner);
    }

    public function hasChannel(string $channelName): bool
    {
        return $this->hasText($channelName);
    }

    public function hasProduct(string $productName): bool
    {
        return $this->hasText($productName);
    }

    public function hasVariantCode(string $variantCode): bool
    {
        return $this->hasText($variantCode);
    }

    public function hasNoItemsMessage(): bool
    {
        return $this->hasText('This wishlist is currently empty.');
    }

    private function hasText(string $text): bool
    {
        return str_contains($this->getDocument()->getText(), $text);
    }
}
