<?php

declare(strict_types=1);

namespace Malina141\SyliusWishlistPlugin\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Sylius\Component\Channel\Model\ChannelInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Core\Model\ShopUserInterface;

class Wishlist implements WishlistInterface
{
    private ?int $id = null;

    private ?ShopUserInterface $owner = null;

    /** @var Collection <int, WishlistItemInterface> */
    private Collection $items;

    private ?ChannelInterface $channel = null;

    public function __construct()
    {
        $this->items = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOwner(): ?ShopUserInterface
    {
        return $this->owner;
    }

    public function setOwner(?ShopUserInterface $owner): void
    {
        $this->owner = $owner;
    }

    /** @return Collection<int, WishlistItemInterface> */
    public function getItems(): Collection
    {
        return $this->items;
    }

    public function getChannel(): ?ChannelInterface
    {
        return $this->channel;
    }

    public function setChannel(?ChannelInterface $channel): void
    {
        $this->channel = $channel;
    }

    public function addItem(WishlistItemInterface $item): void
    {
        if (!$this->items->contains($item)) {
            $this->items->add($item);
            $item->setWishlist($this);
        }
    }

    public function removeItem(WishlistItemInterface $item): void
    {
        if ($this->items->removeElement($item)) {
            if ($item->getWishlist() === $this) {
                $item->setWishlist(null);
            }
        }
    }

    public function hasProductVariant(ProductVariantInterface $productVariant): bool
    {
        return $this->getItemByProductVariant($productVariant) !== null;
    }

    public function getItemByProductVariant(ProductVariantInterface $productVariant): ?WishlistItemInterface
    {
        foreach ($this->items as $existingItem) {
            if ($existingItem->getProductVariant()?->getCode() === $productVariant->getCode()) {
                return $existingItem;
            }
        }

        return null;
    }
}
