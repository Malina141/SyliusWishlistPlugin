<?php

declare(strict_types=1);

namespace Tests\Malina141\SyliusWishlistPlugin\Behat\Element\Shop;

use Sylius\Behat\Element\SyliusElement;

final class ProductWishlistElement extends SyliusElement implements ProductWishlistElementInterface
{
    public function addCurrentProduct(): void
    {
        $this->getElement('current_product_add_button')->press();
        $this->getElement('current_product_remove_button');
    }

    public function removeCurrentProduct(): void
    {
        $this->getElement('current_product_remove_button')->press();
        $this->getElement('current_product_add_button');
    }

    public function addProductFromList(string $productCode): void
    {
        $parameters = ['%productCode%' => $productCode];

        $this->getElement('listed_product_add_button', $parameters)->press();
        $this->getElement('listed_product_remove_button', $parameters);
    }

    public function removeProductFromList(string $productCode): void
    {
        $parameters = ['%productCode%' => $productCode];

        $this->getElement('listed_product_remove_button', $parameters)->press();
        $this->getElement('listed_product_add_button', $parameters);
    }

    public function hasCurrentProductRemoveButton(): bool
    {
        return $this->hasElement('current_product_remove_button');
    }

    protected function getDefinedElements(): array
    {
        return [
            'current_product_add_button' => '[data-test-product-box] [data-test-wishlist-add-button]',
            'current_product_remove_button' => '[data-test-product-box] [data-test-wishlist-remove-button]',
            'listed_product_add_button' => '[data-test-product="%productCode%"] [data-test-wishlist-add-button]',
            'listed_product_remove_button' => '[data-test-product="%productCode%"] [data-test-wishlist-remove-button]',
        ];
    }
}
