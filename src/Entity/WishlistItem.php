<?php

declare(strict_types=1);

namespace Malina141\SyliusWishlistPlugin\Entity;

use Sylius\Component\Core\Model\ProductVariantInterface;

class WishlistItem implements WishlistItemInterface
{
    private ?int $id = null;

    private ?WishlistInterface $wishlist = null;

    private ?ProductVariantInterface $productVariant = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getWishlist(): ?WishlistInterface
    {
        return $this->wishlist;
    }

    public function setWishlist(?WishlistInterface $wishlist): void
    {
        $this->wishlist = $wishlist;
    }

    public function getProductVariant(): ?ProductVariantInterface
    {
        return $this->productVariant;
    }

    public function setProductVariant(?ProductVariantInterface $productVariant): void
    {
        $this->productVariant = $productVariant;
    }
}
