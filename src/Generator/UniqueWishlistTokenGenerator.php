<?php

declare(strict_types=1);

namespace Malina141\SyliusWishlistPlugin\Generator;

use Sylius\Component\User\Security\Generator\GeneratorInterface;

final readonly class UniqueWishlistTokenGenerator implements WishlistTokenGeneratorInterface
{
    public function __construct(
        private GeneratorInterface $tokenGenerator,
    ) {
    }

    public function generate(): string
    {
        return $this->tokenGenerator->generate();
    }
}
