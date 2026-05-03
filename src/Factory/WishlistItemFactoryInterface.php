<?php

declare(strict_types=1);

namespace Malina141\SyliusWishlistPlugin\Factory;

use Malina141\SyliusWishlistPlugin\Entity\WishlistItemInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;

interface WishlistItemFactoryInterface
{
    public function createNew(): WishlistItemInterface;

    public function createForVariant(ProductVariantInterface $productVariant): WishlistItemInterface;
}
