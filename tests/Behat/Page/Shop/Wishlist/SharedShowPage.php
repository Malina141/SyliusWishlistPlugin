<?php

declare(strict_types=1);

namespace Tests\Malina141\SyliusWishlistPlugin\Behat\Page\Shop\Wishlist;

use Sylius\Behat\Page\SyliusPage;

final class SharedShowPage extends SyliusPage implements SharedShowPageInterface
{
    public function getRouteName(): string
    {
        return 'malina141_sylius_wishlist_shop_share_show';
    }

    public function hasSharedWishlistPage(): bool
    {
        return $this->hasElement('page');
    }

    public function hasProduct(string $productName): bool
    {
        return $this->hasElement('product_card', ['%name%' => $productName]);
    }

    public function getProductPrice(string $productName): string
    {
        return trim($this->getElement('product_price', ['%name%' => $productName])->getText());
    }

    public function isAccessDenied(): bool
    {
        return in_array($this->getSession()->getStatusCode(), [403, 404], true);
    }

    protected function getDefinedElements(): array
    {
        return array_merge(parent::getDefinedElements(), [
            'page' => '[data-test-shared-wishlist-page]',
            'product_card' => '[data-test-shared-wishlist-product-card="%name%"]',
            'product_price' => '[data-test-shared-wishlist-product-card="%name%"] [data-test-shared-wishlist-product-price]',
        ]);
    }
}
