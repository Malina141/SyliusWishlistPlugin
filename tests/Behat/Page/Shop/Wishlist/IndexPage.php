<?php

declare(strict_types=1);

namespace Tests\Malina141\SyliusWishlistPlugin\Behat\Page\Shop\Wishlist;

use Behat\Mink\Element\NodeElement;
use Behat\Mink\Exception\ElementNotFoundException;
use Behat\Mink\Session;
use Sylius\Behat\Page\SyliusPage;
use Sylius\Behat\Service\Accessor\TableAccessorInterface;
use Symfony\Component\Routing\RouterInterface;

final class IndexPage extends SyliusPage implements IndexPageInterface
{
    public function __construct(
        Session $session,
        $minkParameters,
        RouterInterface $router,
        private readonly TableAccessorInterface $tableAccessor,
    ) {
        parent::__construct($session, $minkParameters, $router);
    }

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
        $priceElement = $this->getProductRow($productName)->find('css', '[data-test-wishlist-product-price]');

        if (null === $priceElement) {
            throw new ElementNotFoundException($this->getSession(), 'Wishlist product price', 'css', '[data-test-wishlist-product-price]');
        }

        return $priceElement->getText();
    }

    /** @return list<string> */
    public function getProductOptionTexts(string $productName): array
    {
        return array_map(
            static fn (NodeElement $element): string => trim($element->getText()),
            $this->getProductRow($productName)->findAll('css', '[data-test-wishlist-product-option]'),
        );
    }

    public function removeProduct(string $productName): void
    {
        $actions = $this->getProductActions($productName);
        $trigger = $actions->find('css', '[data-bs-toggle="modal"]');

        if (null === $trigger) {
            throw new ElementNotFoundException($this->getSession(), 'Wishlist product delete button', 'css', '[data-bs-toggle="modal"]');
        }

        $modalSelector = $trigger->getAttribute('data-bs-target');
        if (null === $modalSelector || '' === $modalSelector) {
            throw new \RuntimeException(sprintf('Expected product "%s" delete button to reference a confirmation modal.', $productName));
        }

        $trigger->press();
        $this->getDocument()->waitFor(
            5,
            fn (): bool => null !== ($modal = $this->getDocument()->find('css', $modalSelector)) && $modal->isVisible(),
        );

        $confirmButton = $this->getDocument()->find('css', sprintf('%s [data-test-confirm-button]', $modalSelector));
        if (null === $confirmButton) {
            throw new ElementNotFoundException($this->getSession(), 'Wishlist product delete confirmation button', 'css', sprintf('%s [data-test-confirm-button]', $modalSelector));
        }

        $confirmButton->press();
    }

    public function selectProduct(string $productName): void
    {
        $checkbox = $this->getProductRow($productName)->find('css', '.form-check-input[type="checkbox"]');

        if (null === $checkbox) {
            throw new ElementNotFoundException($this->getSession(), 'Wishlist product checkbox', 'css', '.form-check-input[type="checkbox"]');
        }

        $checkbox->check();
    }

    public function selectAllProducts(): void
    {
        $this->getElement('select_all_checkbox')->check();
    }

    public function bulkDeleteSelectedProducts(): void
    {
        $this->pressEnabledButton('bulk_delete_button');
    }

    public function bulkAddSelectedProductsToCart(): void
    {
        $this->pressEnabledButton('bulk_add_to_cart_button');
    }

    public function countItems(): int
    {
        if (!$this->hasElement('table')) {
            return 0;
        }

        return $this->tableAccessor->countTableBodyRows($this->getElement('table'));
    }

    public function hasNoResultsMessage(): bool
    {
        foreach ($this->getDocument()->findAll('css', '[data-test-sylius-flash-message]') as $message) {
            if (str_contains($message->getText(), 'There are no results to display')) {
                return true;
            }
        }

        return false;
    }

    public function getWishlistName(): string
    {
        return trim($this->getElement('wishlist_name')->getText());
    }

    public function renameWishlist(string $name): void
    {
        $this->getElement('wishlist_name_edit_button')->press();
        $this->getDocument()->waitFor(5, fn (): bool => $this->hasElement('wishlist_name_input'));

        $this->getElement('wishlist_name_input')->setValue($name);
        $this->getElement('wishlist_name_save_button')->press();
        $this->getDocument()->waitFor(
            5,
            fn (): bool => $this->hasElement('wishlist_name') && $this->getWishlistName() === $name,
        );
    }

    public function hasNameSavedMessage(): bool
    {
        return $this->hasElement('wishlist_name_saved_message');
    }

    public function reload(): void
    {
        $this->getSession()->reload();
    }

    public function shareWishlist(): void
    {
        $this->pressEnabledButton('share_button');
    }

    public function unshareWishlist(): void
    {
        $this->pressEnabledButton('unshare_button');
    }

    public function isWishlistMarkedAsShared(): bool
    {
        return $this->hasElement('shared_badge');
    }

    public function hasPublicWishlistLink(): bool
    {
        return $this->hasElement('public_link_source');
    }

    public function copyPublicWishlistLink(): void
    {
        $this->getElement('public_link_copy_button')->press();
    }

    public function getPublicWishlistLink(): string
    {
        $link = $this->getElement('public_link_source')->getAttribute('value');

        if (null === $link || '' === $link) {
            throw new \RuntimeException('Expected wishlist public link source to contain a link.');
        }

        return $link;
    }

    private function getProductRow(string $productName): NodeElement
    {
        $row = $this->findProductRow($productName);

        if (null === $row) {
            throw new ElementNotFoundException($this->getSession(), sprintf('Wishlist product "%s" row', $productName), 'table field', 'productVariant.product.name');
        }

        return $row;
    }

    private function findProductRow(string $productName): ?NodeElement
    {
        try {
            $rows = $this->tableAccessor->getRowsWithFields($this->getElement('table'), [
                'productVariant.product.name' => $productName,
            ]);

            return $rows[0] ?? null;
        } catch (ElementNotFoundException|\InvalidArgumentException) {
            return null;
        }
    }

    private function getProductActions(string $productName): NodeElement
    {
        try {
            return $this->tableAccessor->getFieldFromRow(
                $this->getElement('table'),
                $this->getProductRow($productName),
                'actions',
            );
        } catch (\InvalidArgumentException $exception) {
            throw new \RuntimeException(sprintf('Expected product "%s" to have wishlist actions.', $productName), 0, $exception);
        }
    }

    private function pressEnabledButton(string $elementName): void
    {
        $this->getDocument()->waitFor(5, fn (): bool => !$this->getElement($elementName)->hasAttribute('disabled'));
        $this->getElement($elementName)->press();
    }

    protected function getDefinedElements(): array
    {
        return array_merge(parent::getDefinedElements(), [
            'bulk_add_to_cart_button' => '[data-test-bulk-add-to-cart-button]',
            'bulk_delete_button' => '[data-test-bulk-delete-button]',
            'empty_results_message' => '[data-test-sylius-flash-message]',
            'public_link_copy_button' => '[data-test-wishlist-copy-link-button]',
            'public_link_source' => '[data-test-wishlist-public-link-source]',
            'share_button' => '[data-test-wishlist-share-button]',
            'shared_badge' => '[data-test-wishlist-shared-badge]',
            'select_all_checkbox' => '[data-js-bulk-checkboxes]',
            'table' => '[data-test-grid-table]',
            'unshare_button' => '[data-test-wishlist-unshare-button]',
            'wishlist_name' => '[data-test-wishlist-name-display]',
            'wishlist_name_edit_button' => '[data-test-wishlist-name-edit-button]',
            'wishlist_name_input' => '[data-test-wishlist-name-input]',
            'wishlist_name_save_button' => '[data-test-wishlist-name-save-button]',
            'wishlist_name_saved_message' => '[data-test-wishlist-name-success-message]',
        ]);
    }
}
