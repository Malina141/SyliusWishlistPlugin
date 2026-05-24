<?php

declare(strict_types=1);

namespace Malina141\SyliusWishlistPlugin\Generator;

interface WishlistTokenGeneratorInterface
{
    public function generate(): string;
}
