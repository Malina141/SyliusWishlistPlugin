<?php

declare(strict_types=1);

namespace Malina141\SyliusWishlistPlugin\Provider;

interface GuestWishlistTokenProviderInterface
{
    public function provideToken(): string;
}
