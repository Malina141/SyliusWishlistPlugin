<?php

declare(strict_types=1);

namespace Tests\Malina141\SyliusWishlistPlugin\Unit\Context;

use Malina141\SyliusWishlistPlugin\Context\CachedWishlistContext;
use Malina141\SyliusWishlistPlugin\Context\WishlistContextInterface;
use Malina141\SyliusWishlistPlugin\Entity\WishlistInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Service\ResetInterface;

final class CachedWishlistContextTest extends TestCase
{
    public function test_it_is_resettable(): void
    {
        $wishlistContext = $this->createMock(WishlistContextInterface::class);

        $context = new CachedWishlistContext($wishlistContext);

        $this->assertInstanceOf(ResetInterface::class, $context);
    }

    public function test_it_caches_wishlist_until_reset(): void
    {
        $firstWishlist = $this->createMock(WishlistInterface::class);
        $secondWishlist = $this->createMock(WishlistInterface::class);
        $wishlistContext = $this->createMock(WishlistContextInterface::class);
        $wishlistContext
            ->expects($this->exactly(2))
            ->method('getWishlist')
            ->willReturnOnConsecutiveCalls($firstWishlist, $secondWishlist)
        ;

        $context = new CachedWishlistContext($wishlistContext);

        $this->assertSame($firstWishlist, $context->getWishlist());
        $this->assertSame($firstWishlist, $context->getWishlist());

        $context->reset();

        $this->assertSame($secondWishlist, $context->getWishlist());
    }
}
