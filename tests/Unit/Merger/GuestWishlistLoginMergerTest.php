<?php

declare(strict_types=1);

namespace Tests\Malina141\SyliusWishlistPlugin\Unit\Merger;

use Doctrine\ORM\EntityManagerInterface;
use Malina141\SyliusWishlistPlugin\Entity\WishlistInterface;
use Malina141\SyliusWishlistPlugin\Merger\GuestWishlistLoginMerger;
use Malina141\SyliusWishlistPlugin\Merger\WishlistMergerInterface;
use Malina141\SyliusWishlistPlugin\Repository\WishlistRepositoryInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Channel\Context\ChannelContextInterface;
use Sylius\Component\Channel\Model\ChannelInterface;
use Sylius\Component\Core\Model\ShopUserInterface;

final class GuestWishlistLoginMergerTest extends TestCase
{
    private ChannelContextInterface&MockObject $channelContext;

    private WishlistRepositoryInterface&MockObject $wishlistRepository;

    private WishlistMergerInterface&MockObject $wishlistMerger;

    private EntityManagerInterface&MockObject $wishlistManager;

    private GuestWishlistLoginMerger $sut;

    protected function setUp(): void
    {
        $this->channelContext = $this->createMock(ChannelContextInterface::class);
        $this->wishlistRepository = $this->createMock(WishlistRepositoryInterface::class);
        $this->wishlistMerger = $this->createMock(WishlistMergerInterface::class);
        $this->wishlistManager = $this->createMock(EntityManagerInterface::class);

        $this->sut = new GuestWishlistLoginMerger(
            $this->channelContext,
            $this->wishlistRepository,
            $this->wishlistMerger,
            $this->wishlistManager,
        );
    }

    #[Test]
    public function it_does_nothing_when_guest_wishlist_does_not_exist(): void
    {
        $token = 'guest-token';
        $channel = $this->createStub(ChannelInterface::class);
        $shopUser = $this->createStub(ShopUserInterface::class);

        $this->channelContext
            ->expects($this->once())
            ->method('getChannel')
            ->willReturn($channel);
        $this->wishlistRepository
            ->expects($this->once())
            ->method('findOneByTokenAndChannel')
            ->with($token, $channel)
            ->willReturn(null);
        $this->wishlistMerger->expects($this->never())->method('merge');
        $this->wishlistManager->expects($this->never())->method('flush');

        $this->sut->merge($token, $shopUser);
    }

    #[Test]
    public function it_merges_guest_wishlist_and_flushes_changes(): void
    {
        $token = 'guest-token';
        $channel = $this->createStub(ChannelInterface::class);
        $shopUser = $this->createStub(ShopUserInterface::class);
        $guestWishlist = $this->createStub(WishlistInterface::class);

        $this->channelContext
            ->expects($this->once())
            ->method('getChannel')
            ->willReturn($channel);
        $this->wishlistRepository
            ->expects($this->once())
            ->method('findOneByTokenAndChannel')
            ->with($token, $channel)
            ->willReturn($guestWishlist);
        $this->wishlistMerger
            ->expects($this->once())
            ->method('merge')
            ->with($shopUser, $guestWishlist);
        $this->wishlistManager
            ->expects($this->once())
            ->method('flush');

        $this->sut->merge($token, $shopUser);
    }
}
