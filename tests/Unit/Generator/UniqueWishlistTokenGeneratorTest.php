<?php

declare(strict_types=1);

namespace Tests\Malina141\SyliusWishlistPlugin\Unit\Generator;

use Malina141\SyliusWishlistPlugin\Generator\UniqueWishlistTokenGenerator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sylius\Component\User\Security\Generator\GeneratorInterface;

final class UniqueWishlistTokenGeneratorTest extends TestCase
{
    private GeneratorInterface&MockObject $tokenGenerator;

    protected function setUp(): void
    {
        $this->tokenGenerator = $this->createMock(GeneratorInterface::class);
    }

    public function test_it_delegates_token_generation_to_sylius_generator(): void
    {
        $this->tokenGenerator
            ->expects($this->once())
            ->method('generate')
            ->willReturn('unique-wishlist-token')
        ;

        $generator = new UniqueWishlistTokenGenerator($this->tokenGenerator);

        self::assertSame('unique-wishlist-token', $generator->generate());
    }
}
