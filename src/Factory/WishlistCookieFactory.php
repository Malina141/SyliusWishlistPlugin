<?php

declare(strict_types=1);

namespace Malina141\SyliusWishlistPlugin\Factory;

use Malina141\SyliusWishlistPlugin\Options\WishlistCookieOptions;
use Psr\Clock\ClockInterface;
use Symfony\Component\HttpFoundation\Cookie;

final readonly class WishlistCookieFactory implements WishlistCookieFactoryInterface
{
    public function __construct(
        private WishlistCookieOptions $options,
        private ClockInterface $clock,
    ) {
    }

    public function create(string $token): Cookie
    {
        $expires = $this->clock->now()->modify(sprintf('+%d seconds', $this->options->lifetime));

        return Cookie::create($this->options->name)
            ->withValue($token)
            ->withExpires($expires)
            ->withSecure($this->options->secure)
            ->withSameSite($this->options->sameSite)
            ->withPath($this->options->path)
            ->withHttpOnly($this->options->httpOnly);
    }
}
