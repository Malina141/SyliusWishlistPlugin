<?php

declare(strict_types=1);

namespace Malina141\SyliusWishlistPlugin\EventSubscriber;

use Malina141\SyliusWishlistPlugin\Merger\GuestWishlistLoginMergerInterface;
use Malina141\SyliusWishlistPlugin\Options\WishlistCookieOptions;
use Sylius\Component\Core\Model\ShopUserInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

final readonly class MergeWishlistOnLoginSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private WishlistCookieOptions $cookieOptions,
        private GuestWishlistLoginMergerInterface $guestWishlistLoginMerger,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            LoginSuccessEvent::class => 'mergeWishlists',
        ];
    }

    public function mergeWishlists(LoginSuccessEvent $event): void
    {
        $token = $event->getRequest()->cookies->getString($this->cookieOptions->name);
        if ('' === $token) {
            return;
        }

        $user = $event->getUser();
        if (!$user instanceof ShopUserInterface) {
            return;
        }

        $this->guestWishlistLoginMerger->merge($token, $user);

        $event->getResponse()?->headers->clearCookie(
            $this->cookieOptions->name,
            $this->cookieOptions->path,
            null,
            $this->cookieOptions->secure,
            $this->cookieOptions->httpOnly,
            $this->cookieOptions->sameSite,
        );
    }
}
