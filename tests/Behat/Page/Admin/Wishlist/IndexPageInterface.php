<?php

declare(strict_types=1);

namespace Tests\Malina141\SyliusWishlistPlugin\Behat\Page\Admin\Wishlist;

use FriendsOfBehat\PageObjectExtension\Page\SymfonyPageInterface;
use Sylius\Component\Core\Model\ChannelInterface;

interface IndexPageInterface extends SymfonyPageInterface
{
    public function hasWishlistNamed(string $name): bool;

    public function getOwnerForWishlist(string $name): string;

    public function getItemsCountForWishlist(string $name): string;

    public function search(string $phrase): void;

    public function filterByChannel(ChannelInterface $channel): void;

    public function filterByRegisteredCustomers(): void;

    public function filterByGuests(): void;

    public function openWishlistDetails(string $name): void;
}
