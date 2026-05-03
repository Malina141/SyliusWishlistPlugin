<?php

declare(strict_types=1);

namespace Malina141\SyliusWishlistPlugin\Entity;

use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Resource\Model\ResourceInterface;

interface WishlistItemInterface extends ResourceInterface
{
    public function getId(): ?int;

    public function getWishlist(): ?WishlistInterface;

    public function setWishlist(?WishlistInterface $wishlist): void;

    public function getProductVariant(): ?ProductVariantInterface;

    public function setProductVariant(?ProductVariantInterface $productVariant): void;
}
