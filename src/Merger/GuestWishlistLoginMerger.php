<?php

declare(strict_types=1);

namespace Malina141\SyliusWishlistPlugin\Merger;

use Doctrine\ORM\EntityManagerInterface;
use Malina141\SyliusWishlistPlugin\Entity\WishlistInterface;
use Malina141\SyliusWishlistPlugin\Repository\WishlistRepositoryInterface;
use Sylius\Component\Channel\Context\ChannelContextInterface;
use Sylius\Component\Core\Model\ShopUserInterface;

final readonly class GuestWishlistLoginMerger implements GuestWishlistLoginMergerInterface
{
    public function __construct(
        private ChannelContextInterface $channelContext,
        private WishlistRepositoryInterface $wishlistRepository,
        private WishlistMergerInterface $wishlistMerger,
        private EntityManagerInterface $wishlistManager,
    ) {
    }

    public function merge(string $token, ShopUserInterface $user): void
    {
        $channel = $this->channelContext->getChannel();

        $guestWishlist = $this->wishlistRepository->findOneByTokenAndChannel($token, $channel);
        if (!$guestWishlist instanceof WishlistInterface) {
            return;
        }

        $this->wishlistMerger->merge($user, $guestWishlist);
        $this->wishlistManager->flush();
    }
}
