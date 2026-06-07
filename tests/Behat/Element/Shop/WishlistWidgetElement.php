<?php

declare(strict_types=1);

namespace Tests\Malina141\SyliusWishlistPlugin\Behat\Element\Shop;

use Sylius\Behat\Element\SyliusElement;

final class WishlistWidgetElement extends SyliusElement implements WishlistWidgetElementInterface
{
    public function follow(): void
    {
        $this->getElement('link')->click();
    }

    public function getWishlistCount(): int
    {
        if (!$this->hasElement('count')) {
            return 0;
        }

        return (int) trim($this->getElement('count')->getText());
    }

    protected function getDefinedElements(): array
    {
        return [
            'count' => '[data-test-wishlist-widget-count]',
            'link' => '[data-test-wishlist-widget] a',
        ];
    }
}
