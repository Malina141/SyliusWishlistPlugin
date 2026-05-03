<?php

declare(strict_types=1);

namespace Tests\Malina141\SyliusWishlistPlugin\Unit\Context;

use Malina141\SyliusWishlistPlugin\Context\NewWishlistContext;
use Malina141\SyliusWishlistPlugin\Entity\WishlistInterface;
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
    private NewWishlistContext $context;

    protected function setUp(): void
    {
        $this->wishlistFactory = $this->createMock(FactoryInterface::class);
        $this->customerContext = $this->createMock(CustomerContextInterface::class);
        $this->channelContext = $this->createMock(ChannelContextInterface::class);

        $this->context = new NewWishlistContext(
            $this->wishlistFactory,
            $this->customerContext,
            $this->channelContext
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

        $this->assertSame($wishlist, $this->context->getWishlist());
    }
}
