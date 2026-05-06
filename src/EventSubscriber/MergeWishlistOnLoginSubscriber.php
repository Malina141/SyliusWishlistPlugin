<?php

declare(strict_types=1);

namespace Malina141\SyliusWishlistPlugin\EventSubscriber;

use Doctrine\ORM\EntityManagerInterface;
use Malina141\SyliusWishlistPlugin\Entity\WishlistInterface;
use Malina141\SyliusWishlistPlugin\Merger\WishlistMergerInterface;
use Malina141\SyliusWishlistPlugin\Options\WishlistCookieOptions;
use Malina141\SyliusWishlistPlugin\Repository\WishlistRepositoryInterface;
use Sylius\Component\Channel\Context\ChannelContextInterface;
use Sylius\Component\Core\Model\ShopUserInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

final readonly class MergeWishlistOnLoginSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private WishlistCookieOptions $cookieOptions,
        private WishlistRepositoryInterface $wishlistRepository,
        private EntityManagerInterface $wishlistManager,
        private ChannelContextInterface $channelContext,
        private WishlistMergerInterface $wishlistMerger,
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

        $channel = $this->channelContext->getChannel();

        $guestWishlist = $this->wishlistRepository->findOneByTokenAndChannel($token, $channel);
        if (!$guestWishlist instanceof WishlistInterface) {
            return;
        }

        $this->wishlistMerger->merge($user, $guestWishlist);

        $this->wishlistManager->flush();

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
