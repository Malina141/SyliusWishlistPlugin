<?php

declare(strict_types=1);

namespace Malina141\SyliusWishlistPlugin\EventSubscriber;

use Malina141\SyliusWishlistPlugin\Factory\WishlistCookieFactoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final readonly class AttachWishlistCookieTokenSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private WishlistCookieFactoryInterface $wishlistCookieFactory,
        private string $cookieName,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::RESPONSE => 'attachWishlistCookieToken',
        ];
    }

    public function attachWishlistCookieToken(ResponseEvent $event): void
    {
        if (false === $event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $response = $event->getResponse();

        if (false === $request->attributes->has($this->cookieName)) {
            return;
        }

        $token = $request->attributes->getString($this->cookieName);
        $cookie = $this->wishlistCookieFactory->create($token);

        $response->headers->setCookie($cookie);
    }
}
