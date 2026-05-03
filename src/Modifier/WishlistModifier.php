<?php

declare(strict_types=1);

namespace Malina141\SyliusWishlistPlugin\Modifier;

use Malina141\SyliusWishlistPlugin\Entity\WishlistInterface;
use Malina141\SyliusWishlistPlugin\Factory\WishlistItemFactoryInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;

final readonly class WishlistModifier implements WishlistModifierInterface
{
    public function __construct(
        private WishlistItemFactoryInterface $itemFactory,
    )
    {
    }

    public function addVariant(WishlistInterface $wishlist, ProductVariantInterface $variant): void
    {
        if ($wishlist->hasProductVariant($variant)) {
            return;
        }

        $item = $this->itemFactory->createForVariant($variant);
        $wishlist->addItem($item);
    }

    public function removeVariant(WishlistInterface $wishlist, ProductVariantInterface $variant): void
    {
        $item = $wishlist->getItemByProductVariant($variant);
        if (null === $item) {
            return;
        }

        $wishlist->removeItem($item);
    }
}
