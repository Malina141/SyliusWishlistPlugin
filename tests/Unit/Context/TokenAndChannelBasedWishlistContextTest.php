<?php

declare(strict_types=1);

namespace Tests\Malina141\SyliusWishlistPlugin\Unit\Context;

use Malina141\SyliusWishlistPlugin\Context\TokenAndChannelBasedWishlistContext;
use Malina141\SyliusWishlistPlugin\Entity\WishlistInterface;
use Malina141\SyliusWishlistPlugin\Exception\WishlistNotFoundException;
use Malina141\SyliusWishlistPlugin\Provider\WishlistTokenProviderInterface;
use Malina141\SyliusWishlistPlugin\Repository\WishlistRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Channel\Context\ChannelContextInterface;
use Sylius\Component\Channel\Model\ChannelInterface;

final class TokenAndChannelBasedWishlistContextTest extends TestCase
{
    private WishlistTokenProviderInterface&Stub $wishlistTokenProvider;

    private ChannelContextInterface&Stub $channelContext;

    private WishlistRepositoryInterface&MockObject $wishlistRepository;

    private TokenAndChannelBasedWishlistContext $context;

    protected function setUp(): void
    {
        $this->wishlistTokenProvider = $this->createStub(WishlistTokenProviderInterface::class);
        $this->channelContext = $this->createStub(ChannelContextInterface::class);
        $this->wishlistRepository = $this->createMock(WishlistRepositoryInterface::class);

        $this->context = new TokenAndChannelBasedWishlistContext(
            $this->wishlistTokenProvider,
            $this->channelContext,
            $this->wishlistRepository,
        );
    }

    public function test_it_returns_wishlist_if_found_in_repository(): void
    {
        $channel = $this->createMock(ChannelInterface::class);
        $wishlist = $this->createMock(WishlistInterface::class);

        $this->wishlistTokenProvider->method('provideToken')->willReturn('token-123');
        $this->channelContext->method('getChannel')->willReturn($channel);
        $this->wishlistRepository
            ->expects($this->once())
            ->method('findOneByTokenAndChannel')
            ->with('token-123', $channel)
            ->willReturn($wishlist)
        ;

        $this->assertSame($wishlist, $this->context->getWishlist());
    }

    public function test_it_throws_exception_if_wishlist_is_not_found_in_repository(): void
    {
        $channel = $this->createMock(ChannelInterface::class);

        $this->wishlistTokenProvider->method('provideToken')->willReturn('token-123');
        $this->channelContext->method('getChannel')->willReturn($channel);
        $this->wishlistRepository
            ->expects($this->once())
            ->method('findOneByTokenAndChannel')
            ->with('token-123', $channel)
            ->willReturn(null)
        ;

        $this->expectException(WishlistNotFoundException::class);

        $this->context->getWishlist();
    }
}
