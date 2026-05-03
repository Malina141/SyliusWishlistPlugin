<?php

declare(strict_types=1);

namespace Tests\Malina141\SyliusWishlistPlugin\Unit\Context;

use Malina141\SyliusWishlistPlugin\Context\CompositeWishlistContext;
use Malina141\SyliusWishlistPlugin\Context\WishlistContextInterface;
use Malina141\SyliusWishlistPlugin\Entity\WishlistInterface;
use Malina141\SyliusWishlistPlugin\Exception\WishlistNotFoundException;
use PHPUnit\Framework\TestCase;

final class CompositeWishlistContextTest extends TestCase
{
    public function test_it_returns_wishlist_from_the_first_context_that_provides_one(): void
    {
        $wishlist = $this->createMock(WishlistInterface::class);

        $context1 = $this->createMock(WishlistContextInterface::class);
        $context1->method('getWishlist')->willThrowException(new WishlistNotFoundException());

        $context2 = $this->createMock(WishlistContextInterface::class);
        $context2->method('getWishlist')->willReturn($wishlist);

        $context3 = $this->createMock(WishlistContextInterface::class);
        $context3->expects($this->never())->method('getWishlist');

        $compositeContext = new CompositeWishlistContext([$context1, $context2, $context3]);

        $this->assertSame($wishlist, $compositeContext->getWishlist());
    }

    public function test_it_throws_exception_if_no_context_can_provide_a_wishlist(): void
    {
        $context = $this->createMock(WishlistContextInterface::class);
        $context->method('getWishlist')->willThrowException(new WishlistNotFoundException());

        $compositeContext = new CompositeWishlistContext([$context]);

        $this->expectException(WishlistNotFoundException::class);
        $this->expectExceptionMessage('No wishlist could be provided');

        $compositeContext->getWishlist();
    }
}
