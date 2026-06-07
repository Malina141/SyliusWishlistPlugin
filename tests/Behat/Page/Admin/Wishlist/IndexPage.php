<?php

declare(strict_types=1);

namespace Tests\Malina141\SyliusWishlistPlugin\Behat\Page\Admin\Wishlist;

use Behat\Mink\Element\NodeElement;
use Behat\Mink\Exception\ElementNotFoundException;
use Behat\Mink\Session;
use FriendsOfBehat\PageObjectExtension\Page\SymfonyPage;
use Sylius\Behat\Service\Accessor\TableAccessorInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Symfony\Component\Routing\RouterInterface;
use Webmozart\Assert\Assert;

final class IndexPage extends SymfonyPage implements IndexPageInterface
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
        return 'malina141_sylius_wishlist_admin_wishlist_index';
    }

    public function hasWishlistNamed(string $name): bool
    {
        return null !== $this->findWishlistRow($name);
    }

    public function getOwnerForWishlist(string $name): string
    {
        return trim($this->getWishlistField($name, 'owner')->getText());
    }

    public function getItemsCountForWishlist(string $name): string
    {
        return trim($this->getWishlistField($name, 'itemsCount')->getText());
    }

    public function search(string $phrase): void
    {
        $this->getElement('search')->setValue($phrase);
        $this->getElement('filter')->press();
    }

    public function filterByChannel(ChannelInterface $channel): void
    {
        $channelName = $channel->getName();
        Assert::string($channelName);

        $this->getElement('channel_filter')->selectOption($channelName);
        $this->getElement('filter')->press();
    }

    public function filterByRegisteredCustomers(): void
    {
        $this->filterByOwnerValue('1');
    }

    public function filterByGuests(): void
    {
        $this->filterByOwnerValue('0');
    }

    public function openWishlistDetails(string $name): void
    {
        $actions = $this->getWishlistField($name, 'actions');
        $showLink = $actions->find('css', 'a');

        if (null === $showLink) {
            throw new ElementNotFoundException($this->getSession(), 'Wishlist show action', 'css', 'a');
        }

        $showLink->click();
    }

    private function filterByOwnerValue(string $value): void
    {
        $this->getElement('owner_filter')->setValue($value);
        $this->getElement('filter')->press();
    }

    private function getWishlistField(string $name, string $field): NodeElement
    {
        try {
            return $this->tableAccessor->getFieldFromRow(
                $this->getElement('table'),
                $this->getWishlistRow($name),
                $field,
            );
        } catch (\InvalidArgumentException $exception) {
            throw new \RuntimeException(sprintf('Expected wishlist "%s" to have field "%s".', $name, $field), 0, $exception);
        }
    }

    private function getWishlistRow(string $name): NodeElement
    {
        $row = $this->findWishlistRow($name);

        if (null === $row) {
            throw new ElementNotFoundException($this->getSession(), sprintf('Wishlist "%s" row', $name), 'table field', 'name');
        }

        return $row;
    }

    private function findWishlistRow(string $name): ?NodeElement
    {
        try {
            $rows = $this->tableAccessor->getRowsWithFields($this->getElement('table'), ['name' => $name]);

            return $rows[0] ?? null;
        } catch (ElementNotFoundException|\InvalidArgumentException) {
            return null;
        }
    }

    protected function getDefinedElements(): array
    {
        return array_merge(parent::getDefinedElements(), [
            'channel_filter' => '[name="criteria[channel]"]',
            'filter' => '[data-test-filter]',
            'owner_filter' => '[name="criteria[owner]"]',
            'search' => '[data-test-filters-form] [name="criteria[search][value]"]',
            'table' => '.table',
        ]);
    }
}
