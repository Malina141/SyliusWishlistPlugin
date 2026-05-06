<?php

declare(strict_types=1);

namespace Malina141\SyliusWishlistPlugin\Options;

final readonly class WishlistCookieOptions
{
    public function __construct(
        public string $name,
        public int $lifetime,
        public string $path,
        public bool $secure,
        public bool $httpOnly,
        /** @var 'lax'|'strict'|'none'|''|null */
        public ?string $sameSite,
    ) {
    }
}
