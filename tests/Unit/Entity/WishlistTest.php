<?php

declare(strict_types=1);

namespace Tests\Malina141\SyliusWishlistPlugin\Unit\Entity;

use Malina141\SyliusWishlistPlugin\Entity\Wishlist;
use Malina141\SyliusWishlistPlugin\Entity\WishlistItemInterface;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Core\Model\ShopUserInterface;

final class WishlistTest extends TestCase
{
    public function test_it_initializes_with_empty_items_collection_and_no_owner(): void
    {
        $wishlist = new Wishlist();

        $this->assertNull($wishlist->getId());
        $this->assertEmpty($wishlist->getItems());
        $this->assertNull($wishlist->getOwner());
    }

    public function test_it_allows_to_assign_and_retrieve_an_owner(): void
    {
        $wishlist = new Wishlist();

        $shopUser = $this->createMock(ShopUserInterface::class);

        $wishlist->setOwner($shopUser);

        $this->assertNotNull($wishlist->getOwner());
        $this->assertSame($shopUser, $wishlist->getOwner());
    }

    public function test_it_adds_an_item(): void
    {
        $wishlist = new Wishlist();

        $wishlistItemMock = $this->createMock(WishlistItemInterface::class);
        $wishlistItemMock->expects($this->once())->method('setWishlist')->with($wishlist);

        $wishlist->addItem($wishlistItemMock);

        $this->assertContains($wishlistItemMock, $wishlist->getItems());
        $this->assertCount(1, $wishlist->getItems());
    }

    public function test_it_does_not_add_the_same_item_instance_twice(): void
    {
        $wishlist = new Wishlist();

        $wishlistItemMock = $this->createMock(WishlistItemInterface::class);
        $wishlistItemMock->expects($this->once())->method('setWishlist')->with($wishlist);

        $wishlist->addItem($wishlistItemMock);
        $wishlist->addItem($wishlistItemMock);

        $this->assertContains($wishlistItemMock, $wishlist->getItems());
        $this->assertCount(1, $wishlist->getItems());
    }

    public function test_it_removes_an_item(): void
    {
        $wishlist = new Wishlist();

        $wishlistItemMock = $this->createMock(WishlistItemInterface::class);
        $wishlistItemMock->method('getWishlist')->willReturn($wishlist);
        $wishlistItemMock->expects($this->exactly(2))->method('setWishlist');

        $wishlist->addItem($wishlistItemMock);
        $wishlist->removeItem($wishlistItemMock);

        $this->assertEmpty($wishlist->getItems());
        $this->assertNotContains($wishlistItemMock, $wishlist->getItems());
    }

    public function test_it_checks_if_it_has_a_specific_product_variant(): void
    {
        $wishlist = new Wishlist();

        $variantA = $this->createMock(ProductVariantInterface::class);
        $variantB = $this->createMock(ProductVariantInterface::class);

        $wishlistItemMock = $this->createMock(WishlistItemInterface::class);
        $wishlistItemMock->method('getProductVariant')->willReturn($variantA);

        $wishlist->addItem($wishlistItemMock);

        $this->assertTrue($wishlist->hasProductVariant($variantA));
        $this->assertFalse($wishlist->hasProductVariant($variantB));
    }

    public function test_it_gets_item_by_product_variant(): void
    {
        $wishlist = new Wishlist();

        $variantA = $this->createMock(ProductVariantInterface::class);
        $variantB = $this->createMock(ProductVariantInterface::class);

        $wishlistItemMock = $this->createMock(WishlistItemInterface::class);
        $wishlistItemMock->method('getProductVariant')->willReturn($variantA);

        $wishlist->addItem($wishlistItemMock);

        $this->assertSame($wishlistItemMock, $wishlist->getItemByProductVariant($variantA));
        $this->assertNull($wishlist->getItemByProductVariant($variantB));
    }

}
