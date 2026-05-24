<?php

declare(strict_types=1);

namespace Tests\Malina141\SyliusWishlistPlugin\Unit\Provider;

use Malina141\SyliusWishlistPlugin\Generator\WishlistTokenGeneratorInterface;
use Malina141\SyliusWishlistPlugin\Provider\CookieBasedGuestWishlistTokenProvider;
use Override;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Webmozart\Assert\InvalidArgumentException;

final class CookieBasedGuestWishlistTokenProviderTest extends TestCase
{
    private const string COOKIE_NAME = 'COOKIE_NAME';
    private const string TOKEN_VALUE = 'COOKIE_TOKEN';

    private CookieBasedGuestWishlistTokenProvider $guestWishlistTokenProvider;

    private RequestStack&Stub $requestStack;

    private WishlistTokenGeneratorInterface&MockObject $wishlistTokenGenerator;

    #[Override]
    public function setUp(): void
    {
        $this->requestStack = $this->createStub(RequestStack::class);
        $this->wishlistTokenGenerator = $this->createMock(WishlistTokenGeneratorInterface::class);

        $this->guestWishlistTokenProvider = new CookieBasedGuestWishlistTokenProvider(
            $this->requestStack,
            self::COOKIE_NAME,
            $this->wishlistTokenGenerator,
        );
    }

    public function test_it_throws_exception_if_main_request_is_null(): void
    {
        $this->requestStack->method('getMainRequest')->willReturn(null);

        $this->expectException(InvalidArgumentException::class);

        $this->guestWishlistTokenProvider->provideToken();
    }

    public function test_it_returns_token_from_request_attributes(): void
    {
        $request = new Request();

        $request->attributes->set(self::COOKIE_NAME, self::TOKEN_VALUE);

        $this->requestStack->method('getMainRequest')->willReturn($request);
        $this->wishlistTokenGenerator->expects($this->never())->method('generate');

        $this->assertSame(self::TOKEN_VALUE, $this->guestWishlistTokenProvider->provideToken());
    }

    public function test_it_returns_token_from_cookie_and_sets_it_in_attribute(): void
    {
        $request = new Request();

        $request->cookies->set(self::COOKIE_NAME, self::TOKEN_VALUE);

        $this->requestStack->method('getMainRequest')->willReturn($request);
        $this->wishlistTokenGenerator->expects($this->never())->method('generate');

        $this->assertSame(self::TOKEN_VALUE, $this->guestWishlistTokenProvider->provideToken());
        $this->assertSame(self::TOKEN_VALUE, $request->attributes->get(self::COOKIE_NAME));
    }

    public function test_it_generates_token_if_missing_from_cookie_and_attribute_and_sets_it_in_attribute(): void
    {
        $request = new Request();

        $this->requestStack->method('getMainRequest')->willReturn($request);
        $this->wishlistTokenGenerator
            ->expects($this->once())
            ->method('generate')
            ->willReturn(self::TOKEN_VALUE)
        ;

        $this->assertSame(self::TOKEN_VALUE, $this->guestWishlistTokenProvider->provideToken());
        $this->assertSame(self::TOKEN_VALUE, $request->attributes->get(self::COOKIE_NAME));
    }
}
