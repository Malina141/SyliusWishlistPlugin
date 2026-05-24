<?php

declare(strict_types=1);

namespace Malina141\SyliusWishlistPlugin\Context;

use Malina141\SyliusWishlistPlugin\Entity\WishlistInterface;
use Malina141\SyliusWishlistPlugin\Provider\GuestWishlistTokenProviderInterface;
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
        private GuestWishlistTokenProviderInterface $guestWishlistTokenProvider,
    ) {
    }

    public function getWishlist(): WishlistInterface
    {
        $channel = $this->channelContext->getChannel();

        /** @var WishlistInterface $wishlist */
        $wishlist = $this->wishlistFactory->createNew();
        $wishlist->setChannel($channel);

        $customer = $this->customerContext->getCustomer();
        $user = $customer instanceof CustomerInterface ? $customer->getUser() : null;

        if ($user instanceof ShopUserInterface) {
            $wishlist->setOwner($user);
        }

        $token = $this->guestWishlistTokenProvider->provideToken();
        $wishlist->setToken($token);

        return $wishlist;
    }
}
