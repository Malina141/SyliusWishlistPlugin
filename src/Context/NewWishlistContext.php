<?php

declare(strict_types=1);

namespace Malina141\SyliusWishlistPlugin\Context;

use Malina141\SyliusWishlistPlugin\Entity\WishlistInterface;
use Malina141\SyliusWishlistPlugin\Provider\WishlistTokenProviderInterface;
use Sylius\Component\Channel\Context\ChannelContextInterface;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Core\Model\ShopUserInterface;
use Sylius\Component\Customer\Context\CustomerContextInterface;
use Sylius\Resource\Factory\FactoryInterface;

final readonly class NewWishlistContext implements WishlistContextInterface
{
    /**
     * @param FactoryInterface<WishlistInterface> $wishlistFactory
     */
    public function __construct(
        private FactoryInterface $wishlistFactory,
        private CustomerContextInterface $customerContext,
        private ChannelContextInterface $channelContext,
        private WishlistTokenProviderInterface $wishlistTokenProvider,
    ) {
    }

    public function getWishlist(): WishlistInterface
    {
        $channel = $this->channelContext->getChannel();

        /** @var WishlistInterface $wishlist */
        $wishlist = $this->wishlistFactory->createNew();
        $wishlist->setChannel($channel);

        $customer = $this->customerContext->getCustomer();
        if (!$customer instanceof CustomerInterface) {
            $token = $this->wishlistTokenProvider->provideToken();
            $wishlist->setToken($token);

            return $wishlist;
        }

        $user = $customer->getUser();

        if ($user instanceof ShopUserInterface) {
            $wishlist->setOwner($user);

            return $wishlist;
        }

        $token = $this->wishlistTokenProvider->provideToken();
        $wishlist->setToken($token);

        return $wishlist;
    }
}
