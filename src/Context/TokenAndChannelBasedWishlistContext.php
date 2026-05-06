<?php

declare(strict_types=1);

namespace Malina141\SyliusWishlistPlugin\Context;

use Malina141\SyliusWishlistPlugin\Entity\WishlistInterface;
use Malina141\SyliusWishlistPlugin\Exception\WishlistNotFoundException;
use Malina141\SyliusWishlistPlugin\Provider\WishlistTokenProviderInterface;
use Malina141\SyliusWishlistPlugin\Repository\WishlistRepositoryInterface;
use Sylius\Component\Channel\Context\ChannelContextInterface;

final readonly class TokenAndChannelBasedWishlistContext implements WishlistContextInterface
{
    public function __construct(
        private WishlistTokenProviderInterface $wishlistTokenProvider,
        private ChannelContextInterface $channelContext,
        private WishlistRepositoryInterface $wishlistRepository,
    ) {
    }

    public function getWishlist(): WishlistInterface
    {
        $token = $this->wishlistTokenProvider->provideToken();
        $channel = $this->channelContext->getChannel();

        $wishlist = $this->wishlistRepository->findOneByTokenAndChannel($token, $channel);

        if ($wishlist instanceof WishlistInterface) {
            return $wishlist;
        }

        throw new WishlistNotFoundException();
    }
}
