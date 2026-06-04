<?php

declare(strict_types=1);

namespace Tests\Malina141\SyliusWishlistPlugin\Behat\Page\Shop\Wishlist;

use Behat\Mink\Element\NodeElement;
use Behat\Mink\Exception\ElementNotFoundException;
use Sylius\Behat\Page\SyliusPage;

final class IndexPage extends SyliusPage implements IndexPageInterface
{
    public function getRouteName(): string
    {
        return 'malina141_sylius_wishlist_shop_index';
    }

    public function hasProduct(string $productName): bool
    {
        return null !== $this->findProductRow($productName);
    }

    public function getProductPrice(string $productName): string
    {
        $row = $this->getProductRow($productName);
        $priceElement = $row->find('css', '[data-test-wishlist-product-price]');

        if (null === $priceElement) {
            throw new ElementNotFoundException($this->getSession(), 'Wishlist product price', 'css', '[data-test-wishlist-product-price]');
        }

        return $priceElement->getText();
    }

    public function removeProduct(string $productName): void
    {
        $row = $this->getProductRow($productName);
        $confirmButtonSelector = '[data-test-modal="delete"] [data-test-confirm-button]';
        $confirmButton = $row->find('css', $confirmButtonSelector);

        if (null === $confirmButton) {
            throw new ElementNotFoundException(
                $this->getSession(),
                'Wishlist product delete confirmation button',
                'css',
                $confirmButtonSelector,
            );
        }

        $confirmButton->press();
    }

    public function countItems(): int
    {
        $tableBody = $this->getDocument()->find('css', '[data-test-grid-table-body]');

        if (null === $tableBody) {
            return 0;
        }

        return count($tableBody->findAll('css', 'tr'));
    }

    public function hasNoResultsMessage(): bool
    {
        $messages = $this->getDocument()->findAll('css', '[data-test-sylius-flash-message]');

        foreach ($messages as $message) {
            if (str_contains($message->getText(), 'There are no results to display')) {
                return true;
            }
        }

        return false;
    }

    private function getProductRow(string $productName): NodeElement
    {
        $row = $this->findProductRow($productName);

        if (null === $row) {
            throw new ElementNotFoundException($this->getSession(), 'Wishlist product row', 'css', sprintf('[data-test-wishlist-product-name="%s"]', $productName));
        }

        return $row;
    }

    private function findProductRow(string $productName): ?NodeElement
    {
        $productNameElement = $this->getDocument()->find('css', sprintf('[data-test-wishlist-product-name="%s"]', $productName));

        if (null === $productNameElement) {
            return null;
        }

        $row = $productNameElement->getParent();
        while (null !== $row && 'tr' !== $row->getTagName()) {
            $row = $row->getParent();
        }

        return $row;
    }
}
