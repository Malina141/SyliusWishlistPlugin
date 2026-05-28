<?php

declare(strict_types=1);

namespace Tests\Malina141\SyliusWishlistPlugin\Unit\Api\CommandHandler\Shop\Wishlist;

use Malina141\SyliusWishlistPlugin\Adder\WishlistItemsToCartAdderInterface;
use Malina141\SyliusWishlistPlugin\Api\Command\Shop\Wishlist\AddWishlistItemsToCart;
use Malina141\SyliusWishlistPlugin\Api\CommandHandler\Shop\Wishlist\AddWishlistItemsToCartHandler;
use Malina141\SyliusWishlistPlugin\Entity\WishlistInterface;
use Malina141\SyliusWishlistPlugin\Entity\WishlistItemInterface;
use Malina141\SyliusWishlistPlugin\Repository\WishlistItemRepositoryInterface;
use Malina141\SyliusWishlistPlugin\Repository\WishlistRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sylius\Bundle\ApiBundle\Context\UserContextInterface;
use Sylius\Component\Channel\Repository\ChannelRepositoryInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\ShopUserInterface;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class AddWishlistItemsToCartHandlerTest extends TestCase
{
    private WishlistRepositoryInterface&MockObject $wishlistRepository;

    private ChannelRepositoryInterface&MockObject $channelRepository;

    private WishlistItemRepositoryInterface&MockObject $wishlistItemRepository;

    private OrderRepositoryInterface&MockObject $orderRepository;

    private WishlistItemsToCartAdderInterface&MockObject $wishlistItemsToCartAdder;

    private UserContextInterface&MockObject $userContext;

    private AddWishlistItemsToCartHandler $handler;

    protected function setUp(): void
    {
        $this->wishlistRepository = $this->createMock(WishlistRepositoryInterface::class);
        $this->channelRepository = $this->createMock(ChannelRepositoryInterface::class);
        $this->wishlistItemRepository = $this->createMock(WishlistItemRepositoryInterface::class);
        $this->orderRepository = $this->createMock(OrderRepositoryInterface::class);
        $this->wishlistItemsToCartAdder = $this->createMock(WishlistItemsToCartAdderInterface::class);
        $this->userContext = $this->createMock(UserContextInterface::class);

        $this->handler = new AddWishlistItemsToCartHandler(
            $this->wishlistRepository,
            $this->channelRepository,
            $this->wishlistItemRepository,
            $this->orderRepository,
            $this->wishlistItemsToCartAdder,
            $this->userContext,
        );
    }

    public function test_it_rejects_customer_cart_for_anonymous_user(): void
    {
        $command = new AddWishlistItemsToCart('wishlist-token', [1], 'FASHION_WEB', 'cart-token');
        $cart = $this->prepareExistingWishlistAndCart();
        $cartCustomer = $this->createMock(CustomerInterface::class);

        $cart->expects($this->once())->method('isCreatedByGuest')->willReturn(false);
        $cart->expects($this->once())->method('getCustomer')->willReturn($cartCustomer);
        $cartCustomer->expects($this->once())->method('getUser')->willReturn($this->createMock(ShopUserInterface::class));
        $this->userContext->expects($this->once())->method('getUser')->willReturn(null);
        $this->wishlistItemRepository->expects($this->never())->method('findByIdsAndWishlist');
        $this->wishlistItemsToCartAdder->expects($this->never())->method('add');

        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('Cart not found.');

        ($this->handler)($command);
    }

    public function test_it_adds_items_to_cart_owned_by_current_user(): void
    {
        $command = new AddWishlistItemsToCart('wishlist-token', [1], 'FASHION_WEB', 'cart-token');
        $cart = $this->prepareExistingWishlistAndCart();
        $cartCustomer = $this->createMock(CustomerInterface::class);
        $cartUser = $this->createMock(ShopUserInterface::class);
        $currentUser = $this->createMock(ShopUserInterface::class);
        $wishlistItem = $this->createMock(WishlistItemInterface::class);

        $cart->expects($this->once())->method('isCreatedByGuest')->willReturn(false);
        $cart->expects($this->once())->method('getCustomer')->willReturn($cartCustomer);
        $cartCustomer->expects($this->once())->method('getUser')->willReturn($cartUser);
        $cartCustomer->expects($this->exactly(2))->method('getId')->willReturn(10);
        $this->userContext->expects($this->once())->method('getUser')->willReturn($currentUser);
        $currentUser->expects($this->once())->method('getCustomer')->willReturn($cartCustomer);
        $this->wishlistItemRepository
            ->expects($this->once())
            ->method('findByIdsAndWishlist')
            ->with([1], $this->isInstanceOf(WishlistInterface::class))
            ->willReturn([$wishlistItem])
        ;
        $this->wishlistItemsToCartAdder->expects($this->once())->method('add')->with([$wishlistItem], $cart);

        $this->assertSame($cart, ($this->handler)($command));
    }

    private function prepareExistingWishlistAndCart(): OrderInterface&MockObject
    {
        $channel = $this->createMock(ChannelInterface::class);
        $wishlist = $this->createMock(WishlistInterface::class);
        $cart = $this->createMock(OrderInterface::class);

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
        $this->orderRepository
            ->expects($this->once())
            ->method('findCartByTokenValueAndChannel')
            ->with('cart-token', $channel)
            ->willReturn($cart)
        ;

        return $cart;
    }
}
