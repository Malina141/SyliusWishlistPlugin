<?php

declare(strict_types=1);

namespace Malina141\SyliusWishlistPlugin\Factory;

use Symfony\Component\HttpFoundation\Cookie;

interface WishlistCookieFactoryInterface
{
    public function create(string $token): Cookie;
}
