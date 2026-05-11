<?php

declare(strict_types=1);

namespace Malina141\SyliusWishlistPlugin\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Malina141\SyliusWishlistPlugin\SM\WishlistShareStates;
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

    private ?string $token = null;

    private ?string $shareToken = null;

    private string $shareState = WishlistShareStates::STATE_UNSHARED;

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

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(?string $token): void
    {
        $this->token = $token;
    }

    public function getShareToken(): ?string
    {
        return $this->shareToken;
    }

    public function setShareToken(?string $shareToken): void
    {
        $this->shareToken = $shareToken;
    }

    public function getShareState(): string
    {
        return $this->shareState;
    }

    public function setShareState(string $shareState): void
    {
        $this->shareState = $shareState;
    }

    public function isShared(): bool
    {
        return $this->shareState === WishlistShareStates::STATE_SHARED;
    }
}
