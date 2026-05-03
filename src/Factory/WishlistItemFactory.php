<?php

declare(strict_types=1);

namespace Malina141\SyliusWishlistPlugin\Factory;

use Malina141\SyliusWishlistPlugin\Entity\WishlistItemInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Resource\Factory\FactoryInterface;

final readonly class WishlistItemFactory implements WishlistItemFactoryInterface
{
    /**
     * @param FactoryInterface<WishlistItemInterface> $innerFactory
     */
    public function __construct(
        private FactoryInterface $innerFactory,
    )
    {
    }

    public function createNew(): WishlistItemInterface
    {
        /** @var WishlistItemInterface $wishlistItem */
        $wishlistItem = $this->innerFactory->createNew();

        return $wishlistItem;
    }

    public function createForVariant(ProductVariantInterface $productVariant): WishlistItemInterface
    {
        $wishlistItem = $this->createNew();
        $wishlistItem->setProductVariant($productVariant);

        return $wishlistItem;
    }
}
