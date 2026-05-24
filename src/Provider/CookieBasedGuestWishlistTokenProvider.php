<?php

declare(strict_types=1);

namespace Malina141\SyliusWishlistPlugin\Provider;

use Malina141\SyliusWishlistPlugin\Generator\WishlistTokenGeneratorInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Webmozart\Assert\Assert;

final readonly class CookieBasedGuestWishlistTokenProvider implements GuestWishlistTokenProviderInterface
{
    public function __construct(
        private RequestStack $requestStack,
        private string $wishlistCookieName,
        private WishlistTokenGeneratorInterface $wishlistTokenGenerator,
    ) {
    }

    public function provideToken(): string
    {
        $request = $this->requestStack->getMainRequest();
        Assert::notNull($request);

        $token = $request->attributes->getString($this->wishlistCookieName);
        if ('' !== $token) {
            return $token;
        }

        $token = $request->cookies->getString($this->wishlistCookieName);
        if ('' === $token) {
            $token = $this->wishlistTokenGenerator->generate();
        }

        $request->attributes->set($this->wishlistCookieName, $token);

        return $token;
    }
}
