<?php

declare(strict_types=1);

namespace Tests\Malina141\SyliusWishlistPlugin\Unit\Factory;

use DateTimeImmutable;
use DateTimeZone;
use Malina141\SyliusWishlistPlugin\Factory\WishlistCookieFactory;
use Malina141\SyliusWishlistPlugin\Options\WishlistCookieOptions;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Psr\Clock\ClockInterface;

final class WishlistCookieFactoryTest extends TestCase
{
    private const int COOKIE_LIFETIME = 300;

    private ClockInterface&Stub $clock;

    private WishlistCookieOptions $cookieOptions;

    protected function setUp(): void
    {
        $this->clock = $this->createStub(ClockInterface::class);
        $this->cookieOptions = new WishlistCookieOptions(
            name: 'test',
            lifetime: self::COOKIE_LIFETIME,
            path: '/',
            secure: true,
            httpOnly: true,
            sameSite: null,
        );
    }

    #[Test]
    public function expiry_time_is_correctly_calculated(): void
    {
        $date = new DateTimeImmutable('2000-01-01 12:00:00', new DateTimeZone('UTC'));
        $sut = new WishlistCookieFactory($this->cookieOptions, $this->clock);

        $this->clock->method('now')->willReturn($date);

        $cookie = $sut->create('token');

        $this->assertSame($date->modify('+' . self::COOKIE_LIFETIME . ' seconds')->getTimestamp(), $cookie->getExpiresTime());
    }

    #[Test]
    public function all_properties_are_properly_assigned(): void
    {
        $date = new DateTimeImmutable('2000-01-01 12:00:00', new DateTimeZone('UTC'));
        $sut = new WishlistCookieFactory($this->cookieOptions, $this->clock);

        $this->clock->method('now')->willReturn($date);

        $cookie = $sut->create('token');

        $this->assertSame($this->cookieOptions->name, $cookie->getName());
        $this->assertSame('token', $cookie->getValue());
        $this->assertSame($this->cookieOptions->path, $cookie->getPath());
        $this->assertSame($this->cookieOptions->secure, $cookie->isSecure());
        $this->assertSame($this->cookieOptions->httpOnly, $cookie->isHttpOnly());
        $this->assertSame($this->cookieOptions->sameSite, $cookie->getSameSite());
    }
}
