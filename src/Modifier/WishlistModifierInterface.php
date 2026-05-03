<?php

declare(strict_types=1);

namespace Malina141\SyliusWishlistPlugin\Modifier;

use Malina141\SyliusWishlistPlugin\Entity\WishlistInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;

interface WishlistModifierInterface
{
    public function addVariant(WishlistInterface $wishlist, ProductVariantInterface $variant): void;

    public function removeVariant(WishlistInterface $wishlist, ProductVariantInterface $variant): void;
}
