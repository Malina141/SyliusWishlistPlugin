<?php

declare(strict_types=1);

namespace Tests\Malina141\SyliusWishlistPlugin\Unit\Context;

use Malina141\SyliusWishlistPlugin\Context\CustomerBasedWishlistContext;
use Malina141\SyliusWishlistPlugin\Entity\WishlistInterface;
use Malina141\SyliusWishlistPlugin\Exception\WishlistNotFoundException;
use Malina141\SyliusWishlistPlugin\Repository\WishlistRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Channel\Context\ChannelContextInterface;
use Sylius\Component\Channel\Model\ChannelInterface;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Core\Model\ShopUserInterface;
use Sylius\Component\Customer\Context\CustomerContextInterface;

final class CustomerBasedWishlistContextTest extends TestCase
{
    private CustomerContextInterface&MockObject $customerContext;
    private WishlistRepositoryInterface&MockObject $wishlistRepository;
    private ChannelContextInterface&MockObject $channelContext;
    private CustomerBasedWishlistContext $context;

    protected function setUp(): void
    {
        $this->customerContext = $this->createMock(CustomerContextInterface::class);
        $this->wishlistRepository = $this->createMock(WishlistRepositoryInterface::class);
        $this->channelContext = $this->createMock(ChannelContextInterface::class);

        $this->context = new CustomerBasedWishlistContext(
            $this->customerContext,
            $this->wishlistRepository,
            $this->channelContext
        );
    }

    public function test_it_throws_exception_if_no_customer_is_found(): void
    {
        $this->customerContext->expects($this->once())->method('getCustomer')->willReturn(null);

        $this->expectException(WishlistNotFoundException::class);

        $this->context->getWishlist();
    }

    public function test_it_throws_exception_if_customer_has_no_user_account(): void
    {
        $customer = $this->createMock(CustomerInterface::class);
        $customer->expects($this->once())->method('getUser')->willReturn(null);

        $this->customerContext->expects($this->once())->method('getCustomer')->willReturn($customer);

        $this->expectException(WishlistNotFoundException::class);

        $this->context->getWishlist();
    }

    public function test_it_throws_exception_if_wishlist_is_not_found_in_repository(): void
    {
        $user = $this->createMock(ShopUserInterface::class);
        $customer = $this->createMock(CustomerInterface::class);
        $customer->expects($this->once())->method('getUser')->willReturn($user);
        $channel = $this->createMock(ChannelInterface::class);

        $this->customerContext->expects($this->once())->method('getCustomer')->willReturn($customer);
        $this->channelContext->expects($this->once())->method('getChannel')->willReturn($channel);

        $this->wishlistRepository->expects($this->once())->method('findOneByOwnerAndChannel')->with($user, $channel)->willReturn(null);

        $this->expectException(WishlistNotFoundException::class);

        $this->context->getWishlist();
    }

    public function test_it_returns_wishlist_if_found_in_repository(): void
    {
        $user = $this->createMock(ShopUserInterface::class);
        $customer = $this->createMock(CustomerInterface::class);
        $customer->expects($this->once())->method('getUser')->willReturn($user);
        $channel = $this->createMock(ChannelInterface::class);
        $wishlist = $this->createMock(WishlistInterface::class);

        $this->customerContext->expects($this->once())->method('getCustomer')->willReturn($customer);
        $this->channelContext->expects($this->once())->method('getChannel')->willReturn($channel);

        $this->wishlistRepository->expects($this->once())->method('findOneByOwnerAndChannel')->with($user, $channel)->willReturn($wishlist);

        $this->assertSame($wishlist, $this->context->getWishlist());
    }
}
