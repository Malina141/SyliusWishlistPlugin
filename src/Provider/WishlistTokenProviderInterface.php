<?php

declare(strict_types=1);

namespace Malina141\SyliusWishlistPlugin\Provider;

interface WishlistTokenProviderInterface
{
    public function provideToken(): string;
}
