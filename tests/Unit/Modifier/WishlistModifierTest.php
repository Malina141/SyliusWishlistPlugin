<?php

declare(strict_types=1);

namespace Tests\Malina141\SyliusWishlistPlugin\Unit\Modifier;

use Malina141\SyliusWishlistPlugin\Entity\WishlistInterface;
use Malina141\SyliusWishlistPlugin\Entity\WishlistItemInterface;
use Malina141\SyliusWishlistPlugin\Factory\WishlistItemFactoryInterface;
use Malina141\SyliusWishlistPlugin\Modifier\WishlistModifier;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\ProductVariantInterface;

final class WishlistModifierTest extends TestCase
{
    private WishlistItemFactoryInterface&MockObject $itemFactory;
    private WishlistModifier $modifier;

    protected function setUp(): void
    {
        $this->itemFactory = $this->createMock(WishlistItemFactoryInterface::class);

        $this->modifier = new WishlistModifier($this->itemFactory);
    }

    public function test_it_does_nothing_if_variant_is_already_in_wishlist(): void
    {
        $wishlist = $this->createMock(WishlistInterface::class);
        $variant = $this->createMock(ProductVariantInterface::class);

        $wishlist->expects($this->once())->method('hasProductVariant')->with($variant)->willReturn(true);

        $this->itemFactory->expects($this->never())->method('createForVariant');
        $wishlist->expects($this->never())->method('addItem');

        $this->modifier->addVariant($wishlist, $variant);
    }

    public function test_it_adds_new_item_to_wishlist_if_variant_is_not_present(): void
    {
        $wishlist = $this->createMock(WishlistInterface::class);
        $variant = $this->createMock(ProductVariantInterface::class);
        $item = $this->createMock(WishlistItemInterface::class);

        $wishlist->expects($this->once())->method('hasProductVariant')->with($variant)->willReturn(false);
        $this->itemFactory->expects($this->once())->method('createForVariant')->with($variant)->willReturn($item);
        $wishlist->expects($this->once())->method('addItem')->with($item);

        $this->modifier->addVariant($wishlist, $variant);
    }

    public function test_it_does_nothing_when_removing_variant_that_is_not_in_wishlist(): void
    {
        $wishlist = $this->createMock(WishlistInterface::class);
        $variant = $this->createMock(ProductVariantInterface::class);

        $wishlist->expects($this->once())->method('getItemByProductVariant')->with($variant)->willReturn(null);

        $wishlist->expects($this->never())->method('removeItem');

        $this->modifier->removeVariant($wishlist, $variant);
    }

    public function test_it_removes_item_from_wishlist_if_present(): void
    {
        $wishlist = $this->createMock(WishlistInterface::class);
        $variant = $this->createMock(ProductVariantInterface::class);
        $item = $this->createMock(WishlistItemInterface::class);

        $wishlist->expects($this->once())->method('getItemByProductVariant')->with($variant)->willReturn($item);
        $wishlist->expects($this->once())->method('removeItem')->with($item);

        $this->modifier->removeVariant($wishlist, $variant);
    }
}
