<?php

declare(strict_types=1);

namespace Tests\Malina141\SyliusWishlistPlugin\Unit\Provider;

use Malina141\SyliusWishlistPlugin\Entity\Wishlist;
use Malina141\SyliusWishlistPlugin\Generator\WishlistTokenGeneratorInterface;
use Malina141\SyliusWishlistPlugin\Provider\WishlistShareTokenProvider;
use Override;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;

final class WishlistShareTokenProviderTest extends TestCase
{
    private WishlistShareTokenProvider $wishlistShareTokenProvider;

    private WishlistTokenGeneratorInterface&Stub $tokenGenerator;

    #[Override]
    protected function setUp(): void
    {
        $this->tokenGenerator = $this->createStub(WishlistTokenGeneratorInterface::class);

        $this->wishlistShareTokenProvider = new WishlistShareTokenProvider($this->tokenGenerator);
    }

    public function test_it_returns_existing_share_token(): void
    {
        $shareToken = 'SHARE_TOKEN';

        $wishlist = new Wishlist();
        $wishlist->setShareToken($shareToken);

        $this->assertSame($shareToken, $this->wishlistShareTokenProvider->provideShareToken($wishlist));
    }

    public function test_it_generates_and_assigns_share_token_when_missing(): void
    {
        $shareToken = 'SHARE_TOKEN';

        $wishlist = new Wishlist();

        $this->tokenGenerator->method('generate')->willReturn($shareToken);

        $this->assertSame($shareToken, $this->wishlistShareTokenProvider->provideShareToken($wishlist));
        $this->assertSame($shareToken, $wishlist->getShareToken());
    }
}
