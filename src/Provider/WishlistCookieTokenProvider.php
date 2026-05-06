<?php

declare(strict_types=1);

namespace Malina141\SyliusWishlistPlugin\Provider;

use Sylius\Resource\Generator\RandomnessGeneratorInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Webmozart\Assert\Assert;

final readonly class WishlistCookieTokenProvider implements WishlistTokenProviderInterface
{
    public function __construct(
        private RequestStack $requestStack,
        private string $wishlistCookieName,
        private RandomnessGeneratorInterface $randomnessGenerator,
        private int $tokenLength = 32,
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
            $token = $this->randomnessGenerator->generateUriSafeString($this->tokenLength);
        }

        $request->attributes->set($this->wishlistCookieName, $token);

        return $token;
    }
}
