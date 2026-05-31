<?php

declare(strict_types=1);

namespace Tests\Malina141\SyliusWishlistPlugin\Unit\Api\CommandHandler\Shop\Wishlist;

use Malina141\SyliusWishlistPlugin\Adder\WishlistItemsToCartAdderInterface;
use Malina141\SyliusWishlistPlugin\Api\Command\Shop\Wishlist\AddWishlistItemsToCart;
use Malina141\SyliusWishlistPlugin\Api\CommandHandler\Shop\Wishlist\AddWishlistItemsToCartHandler;
use Malina141\SyliusWishlistPlugin\Api\Security\CartAccessCheckerInterface;
use Malina141\SyliusWishlistPlugin\Api\Security\WishlistAccessCheckerInterface;
use Malina141\SyliusWishlistPlugin\Entity\WishlistInterface;
use Malina141\SyliusWishlistPlugin\Entity\WishlistItemInterface;
use Malina141\SyliusWishlistPlugin\Repository\WishlistItemRepositoryInterface;
use Malina141\SyliusWishlistPlugin\Repository\WishlistRepositoryInterface;
use Override;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Channel\Repository\ChannelRepositoryInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class AddWishlistItemsToCartHandlerTest extends TestCase
{
    private WishlistRepositoryInterface&MockObject $wishlistRepository;

    private ChannelRepositoryInterface&MockObject $channelRepository;

    private WishlistItemRepositoryInterface&MockObject $wishlistItemRepository;

    private OrderRepositoryInterface&MockObject $orderRepository;

    private WishlistItemsToCartAdderInterface&MockObject $wishlistItemsToCartAdder;

    private WishlistAccessCheckerInterface&MockObject $wishlistAccessChecker;

    private CartAccessCheckerInterface&MockObject $cartAccessChecker;

    private AddWishlistItemsToCartHandler $handler;

    #[Override]
    protected function setUp(): void
    {
        $this->wishlistRepository = $this->createMock(WishlistRepositoryInterface::class);
        $this->channelRepository = $this->createMock(ChannelRepositoryInterface::class);
        $this->wishlistItemRepository = $this->createMock(WishlistItemRepositoryInterface::class);
        $this->orderRepository = $this->createMock(OrderRepositoryInterface::class);
        $this->wishlistItemsToCartAdder = $this->createMock(WishlistItemsToCartAdderInterface::class);
        $this->wishlistAccessChecker = $this->createMock(WishlistAccessCheckerInterface::class);
        $this->cartAccessChecker = $this->createMock(CartAccessCheckerInterface::class);

        $this->handler = new AddWishlistItemsToCartHandler(
            $this->wishlistRepository,
            $this->channelRepository,
            $this->wishlistItemRepository,
            $this->orderRepository,
            $this->wishlistItemsToCartAdder,
            $this->wishlistAccessChecker,
            $this->cartAccessChecker,
        );
    }

    #[Test]
    public function it_throws_not_found_when_wishlist_does_not_exist(): void
    {
        $channel = $this->createMock(ChannelInterface::class);

        $this->channelRepository
            ->expects($this->once())
            ->method('findOneByCode')
            ->with('FASHION_WEB')
            ->willReturn($channel)
        ;
        $this->wishlistRepository
            ->expects($this->once())
            ->method('findOneByTokenAndChannel')
            ->with('wishlist-token', $channel)
            ->willReturn(null)
        ;
        $this->wishlistAccessChecker->expects($this->never())->method('canAccessPrivateToken');
        $this->orderRepository->expects($this->never())->method('findCartByTokenValueAndChannel');
        $this->wishlistItemsToCartAdder->expects($this->never())->method('add');

        $this->expectException(NotFoundHttpException::class);

        ($this->handler)(new AddWishlistItemsToCart('wishlist-token', [1], 'FASHION_WEB', 'cart-token'));
    }

    #[Test]
    public function it_throws_not_found_when_wishlist_is_not_accessible(): void
    {
        $wishlist = $this->prepareExistingWishlist();

        $this->wishlistAccessChecker
            ->expects($this->once())
            ->method('canAccessPrivateToken')
            ->with($wishlist)
            ->willReturn(false)
        ;
        $this->orderRepository->expects($this->never())->method('findCartByTokenValueAndChannel');
        $this->wishlistItemsToCartAdder->expects($this->never())->method('add');

        $this->expectException(NotFoundHttpException::class);

        ($this->handler)(new AddWishlistItemsToCart('wishlist-token', [1], 'FASHION_WEB', 'cart-token'));
    }

    #[Test]
    public function it_throws_not_found_when_cart_does_not_exist(): void
    {
        $channel = $this->createMock(ChannelInterface::class);
        $wishlist = $this->createMock(WishlistInterface::class);

        $this->prepareExistingWishlist($channel, $wishlist);
        $this->wishlistAccessChecker
            ->expects($this->once())
            ->method('canAccessPrivateToken')
            ->with($wishlist)
            ->willReturn(true)
        ;
        $this->orderRepository
            ->expects($this->once())
            ->method('findCartByTokenValueAndChannel')
            ->with('cart-token', $channel)
            ->willReturn(null)
        ;
        $this->cartAccessChecker->expects($this->never())->method('canAccess');
        $this->wishlistItemsToCartAdder->expects($this->never())->method('add');

        $this->expectException(NotFoundHttpException::class);

        ($this->handler)(new AddWishlistItemsToCart('wishlist-token', [1], 'FASHION_WEB', 'cart-token'));
    }

    #[Test]
    public function it_throws_not_found_when_cart_is_not_accessible(): void
    {
        $cart = $this->prepareExistingWishlistAndCart();

        $this->cartAccessChecker
            ->expects($this->once())
            ->method('canAccess')
            ->with($cart)
            ->willReturn(false)
        ;
        $this->wishlistItemRepository->expects($this->never())->method('findByIdsAndWishlist');
        $this->wishlistItemsToCartAdder->expects($this->never())->method('add');

        $this->expectException(NotFoundHttpException::class);

        ($this->handler)(new AddWishlistItemsToCart('wishlist-token', [1], 'FASHION_WEB', 'cart-token'));
    }

    #[Test]
    public function it_returns_accessible_cart_without_adding_items_when_selection_is_empty(): void
    {
        $cart = $this->prepareExistingWishlistAndCart();

        $this->cartAccessChecker
            ->expects($this->once())
            ->method('canAccess')
            ->with($cart)
            ->willReturn(true)
        ;
        $this->wishlistItemRepository->expects($this->never())->method('findByIdsAndWishlist');
        $this->wishlistItemsToCartAdder->expects($this->never())->method('add');

        $this->assertSame($cart, ($this->handler)(new AddWishlistItemsToCart('wishlist-token', [], 'FASHION_WEB', 'cart-token')));
    }

    #[Test]
    public function it_adds_items_to_accessible_cart(): void
    {
        $wishlist = $this->createMock(WishlistInterface::class);
        $cart = $this->prepareExistingWishlistAndCart(wishlist: $wishlist);
        $wishlistItem = $this->createMock(WishlistItemInterface::class);

        $this->cartAccessChecker
            ->expects($this->once())
            ->method('canAccess')
            ->with($cart)
            ->willReturn(true)
        ;
        $this->wishlistItemRepository
            ->expects($this->once())
            ->method('findByIdsAndWishlist')
            ->with([1], $wishlist)
            ->willReturn([$wishlistItem])
        ;
        $this->wishlistItemsToCartAdder->expects($this->once())->method('add')->with([$wishlistItem], $cart);

        $this->assertSame($cart, ($this->handler)(new AddWishlistItemsToCart('wishlist-token', [1], 'FASHION_WEB', 'cart-token')));
    }

    private function prepareExistingWishlistAndCart(?WishlistInterface $wishlist = null): OrderInterface&MockObject
    {
        $channel = $this->createMock(ChannelInterface::class);
        $wishlist ??= $this->createMock(WishlistInterface::class);
        $cart = $this->createMock(OrderInterface::class);

        $this->prepareExistingWishlist($channel, $wishlist);
        $this->wishlistAccessChecker
            ->expects($this->once())
            ->method('canAccessPrivateToken')
            ->with($wishlist)
            ->willReturn(true)
        ;
        $this->orderRepository
            ->expects($this->once())
            ->method('findCartByTokenValueAndChannel')
            ->with('cart-token', $channel)
            ->willReturn($cart)
        ;

        return $cart;
    }

    private function prepareExistingWishlist(
        ?ChannelInterface $channel = null,
        ?WishlistInterface $wishlist = null,
    ): WishlistInterface {
        $channel ??= $this->createMock(ChannelInterface::class);
        $wishlist ??= $this->createMock(WishlistInterface::class);

        $this->channelRepository
            ->expects($this->once())
            ->method('findOneByCode')
            ->with('FASHION_WEB')
            ->willReturn($channel)
        ;
        $this->wishlistRepository
            ->expects($this->once())
            ->method('findOneByTokenAndChannel')
            ->with('wishlist-token', $channel)
            ->willReturn($wishlist)
        ;

        return $wishlist;
    }
}
