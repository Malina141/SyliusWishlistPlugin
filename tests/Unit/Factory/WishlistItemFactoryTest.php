<?php

declare(strict_types=1);

namespace Tests\Malina141\SyliusWishlistPlugin\Unit\Factory;

use Malina141\SyliusWishlistPlugin\Entity\WishlistItemInterface;
use Malina141\SyliusWishlistPlugin\Factory\WishlistItemFactory;
use Malina141\SyliusWishlistPlugin\Factory\WishlistItemFactoryInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Resource\Factory\FactoryInterface;

final class WishlistItemFactoryTest extends TestCase
{
    private FactoryInterface&Stub $innerFactory;

    private WishlistItemFactory $sut;

    protected function setUp(): void
    {
        $this->innerFactory = $this->createStub(FactoryInterface::class);
        $this->sut = new WishlistItemFactory($this->innerFactory);
    }

    #[Test]
    public function it_implements_wishlist_item_factory_interface(): void
    {
        $this->assertInstanceOf(WishlistItemFactoryInterface::class, $this->sut);
    }

    #[Test]
    public function create_new_delegates_to_inner_factory(): void
    {
        $wishlistItem = $this->createStub(WishlistItemInterface::class);
        $this->innerFactory->method('createNew')->willReturn($wishlistItem);

        $result = $this->sut->createNew();

        $this->assertSame($wishlistItem, $result);
    }

    #[Test]
    public function create_for_variant_sets_product_variant_on_item(): void
    {
        $wishlistItem = $this->createMock(WishlistItemInterface::class);
        $productVariant = $this->createStub(ProductVariantInterface::class);

        $this->innerFactory->method('createNew')->willReturn($wishlistItem);

        $wishlistItem
            ->expects($this->once())
            ->method('setProductVariant')
            ->with($productVariant);

        $result = $this->sut->createForVariant($productVariant);

        $this->assertSame($wishlistItem, $result);
    }
}
