<?php

declare(strict_types=1);

namespace Malina141\SyliusWishlistPlugin\Entity;

use Doctrine\Common\Collections\Collection;
use Sylius\Component\Channel\Model\ChannelAwareInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Core\Model\ShopUserInterface;
use Sylius\Resource\Model\ResourceInterface;

interface WishlistInterface extends ResourceInterface, ChannelAwareInterface
{
    public function getId(): ?int;

    public function getOwner(): ?ShopUserInterface;

    public function setOwner(?ShopUserInterface $owner): void;

    /** @return Collection<int, WishlistItemInterface> */
    public function getItems(): Collection;

    public function addItem(WishlistItemInterface $item): void;

    public function removeItem(WishlistItemInterface $item): void;

    public function hasProductVariant(ProductVariantInterface $productVariant): bool;

    public function getItemByProductVariant(ProductVariantInterface $productVariant): ?WishlistItemInterface;
}
