<?php

declare(strict_types=1);

namespace Tests\Malina141\SyliusWishlistPlugin\Unit\Merger;

use Doctrine\ORM\EntityManagerInterface;
use Malina141\SyliusWishlistPlugin\Entity\Wishlist;
use Malina141\SyliusWishlistPlugin\Entity\WishlistItem;
use Malina141\SyliusWishlistPlugin\Merger\WishlistMerger;
use Malina141\SyliusWishlistPlugin\Repository\WishlistRepositoryInterface;
use Override;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Core\Model\ShopUserInterface;

final class WishlistMergerTest extends TestCase
{
    private WishlistMerger $wishlistMerger;

    private WishlistRepositoryInterface&MockObject $wishlistRepository;

    private EntityManagerInterface&MockObject $entityManager;

    #[Override]
    protected function setUp(): void
    {
        $this->wishlistRepository = $this->createMock(WishlistRepositoryInterface::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);

        $this->wishlistMerger = new WishlistMerger($this->wishlistRepository, $this->entityManager);
    }

    public function test_it_does_nothing_if_guest_wishlist_has_no_channel(): void
    {
        $wishlist = new Wishlist();
        $shopUser = $this->createStub(ShopUserInterface::class);

        $this->wishlistRepository->expects($this->never())->method('findOneByOwnerAndChannel');

        $this->wishlistMerger->merge($shopUser, $wishlist);
    }

    public function test_it_assigns_guest_wishlist_to_user_if_user_has_no_wishlist_in_this_channel(): void
    {
        $channel = $this->createStub(ChannelInterface::class);

        $wishlist = new Wishlist();
        $wishlist->setChannel($channel);
        $wishlist->setToken('TEST_TOKEN');

        $shopUser = $this->createStub(ShopUserInterface::class);

        $this->wishlistRepository->method('findOneByOwnerAndChannel')->willReturn(null);

        $this->wishlistMerger->merge($shopUser, $wishlist);

        $this->assertSame($shopUser, $wishlist->getOwner());
        $this->assertSame('TEST_TOKEN', $wishlist->getToken());
    }

    public function test_it_moves_items_and_removes_guest_wishlist_if_user_already_has_wishlist(): void
    {
        $channel = $this->createStub(ChannelInterface::class);
        $productVariant = $this->createStub(ProductVariantInterface::class);

        $guestWishlist = new Wishlist();
        $guestWishlist->setChannel($channel);

        $wishlistItem = new WishlistItem();
        $wishlistItem->setProductVariant($productVariant);

        $guestWishlist->addItem($wishlistItem);

        $shopUser = $this->createStub(ShopUserInterface::class);

        $userWishlist = new Wishlist();
        $userWishlist->setChannel($channel);
        $userWishlist->setOwner($shopUser);

        $this->wishlistRepository->method('findOneByOwnerAndChannel')->willReturn($userWishlist);

        $this->entityManager->expects($this->once())->method('remove')->with($guestWishlist);

        $this->wishlistMerger->merge($shopUser, $guestWishlist);

        $this->assertSame($userWishlist, $wishlistItem->getWishlist());
    }

    public function test_it_does_not_move_item_if_user_wishlist_already_has_same_product_variant(): void
    {
        $channel = $this->createStub(ChannelInterface::class);
        $productVariant = $this->createStub(ProductVariantInterface::class);

        $guestWishlist = new Wishlist();
        $guestWishlist->setChannel($channel);

        $guestWishlistItem = new WishlistItem();
        $guestWishlistItem->setProductVariant($productVariant);

        $guestWishlist->addItem($guestWishlistItem);

        $shopUser = $this->createStub(ShopUserInterface::class);

        $userWishlist = new Wishlist();
        $userWishlist->setChannel($channel);
        $userWishlist->setOwner($shopUser);

        $userWishlistItem = new WishlistItem();
        $userWishlistItem->setProductVariant($productVariant);

        $userWishlist->addItem($userWishlistItem);

        $this->wishlistRepository->method('findOneByOwnerAndChannel')->willReturn($userWishlist);

        $this->wishlistMerger->merge($shopUser, $guestWishlist);

        $this->assertSame($guestWishlist, $guestWishlistItem->getWishlist());
        $this->assertCount(1, $userWishlist->getItems());
        $this->assertCount(1, $guestWishlist->getItems());
    }
}
