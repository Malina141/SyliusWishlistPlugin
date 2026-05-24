<?php

declare(strict_types=1);

namespace Tests\Malina141\SyliusWishlistPlugin\Unit\Context;

use Malina141\SyliusWishlistPlugin\Context\NewWishlistContext;
use Malina141\SyliusWishlistPlugin\Entity\WishlistInterface;
use Malina141\SyliusWishlistPlugin\Provider\WishlistTokenProviderInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Channel\Context\ChannelContextInterface;
use Sylius\Component\Channel\Model\ChannelInterface;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Core\Model\ShopUserInterface;
use Sylius\Component\Customer\Context\CustomerContextInterface;
use Sylius\Resource\Factory\FactoryInterface;

final class NewWishlistContextTest extends TestCase
{
    private FactoryInterface&MockObject $wishlistFactory;
    private CustomerContextInterface&MockObject $customerContext;
    private ChannelContextInterface&MockObject $channelContext;
    private WishlistTokenProviderInterface&MockObject $wishlistTokenProvider;
    private NewWishlistContext $context;

    protected function setUp(): void
    {
        $this->wishlistFactory = $this->createMock(FactoryInterface::class);
        $this->customerContext = $this->createMock(CustomerContextInterface::class);
        $this->channelContext = $this->createMock(ChannelContextInterface::class);
        $this->wishlistTokenProvider = $this->createMock(WishlistTokenProviderInterface::class);

        $this->context = new NewWishlistContext(
            $this->wishlistFactory,
            $this->customerContext,
            $this->channelContext,
            $this->wishlistTokenProvider,
        );
    }

    public function test_it_creates_a_new_wishlist_with_channel_for_guest(): void
    {
        $channel = $this->createMock(ChannelInterface::class);
        $wishlist = $this->createMock(WishlistInterface::class);

        $this->channelContext->expects($this->once())->method('getChannel')->willReturn($channel);
        $this->wishlistFactory->expects($this->once())->method('createNew')->willReturn($wishlist);

        $wishlist->expects($this->once())->method('setChannel')->with($channel);
        $this->customerContext->expects($this->once())->method('getCustomer')->willReturn(null);
        $wishlist->expects($this->never())->method('setOwner');
        $this->wishlistTokenProvider->expects($this->once())->method('provideToken')->willReturn('test-token');
        $wishlist->expects($this->once())->method('setToken')->with('test-token');

        $this->assertSame($wishlist, $this->context->getWishlist());
    }

    public function test_it_creates_a_new_wishlist_with_channel_and_owner_for_logged_in_user(): void
    {
        $channel = $this->createMock(ChannelInterface::class);
        $wishlist = $this->createMock(WishlistInterface::class);
        $user = $this->createMock(ShopUserInterface::class);
        $customer = $this->createMock(CustomerInterface::class);
        $customer->expects($this->once())->method('getUser')->willReturn($user);

        $this->channelContext->expects($this->once())->method('getChannel')->willReturn($channel);
        $this->wishlistFactory->expects($this->once())->method('createNew')->willReturn($wishlist);

        $wishlist->expects($this->once())->method('setChannel')->with($channel);
        $this->customerContext->expects($this->once())->method('getCustomer')->willReturn($customer);
        $wishlist->expects($this->once())->method('setOwner')->with($user);
        $this->wishlistTokenProvider->expects($this->once())->method('provideToken')->willReturn('test-token');
        $wishlist->expects($this->once())->method('setToken')->with('test-token');

        $this->assertSame($wishlist, $this->context->getWishlist());
    }
}
