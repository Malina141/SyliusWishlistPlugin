<?php

declare(strict_types=1);

namespace Malina141\SyliusWishlistPlugin\Context;

use Malina141\SyliusWishlistPlugin\Entity\WishlistInterface;
use Malina141\SyliusWishlistPlugin\Exception\WishlistNotFoundException;
use Malina141\SyliusWishlistPlugin\Repository\WishlistRepositoryInterface;
use Sylius\Component\Channel\Context\ChannelContextInterface;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Core\Model\ShopUserInterface;
use Sylius\Component\Customer\Context\CustomerContextInterface;

final readonly class CustomerBasedWishlistContext implements WishlistContextInterface
{
    public function __construct(
        private CustomerContextInterface    $customerContext,
        private WishlistRepositoryInterface $wishlistRepository,
        private ChannelContextInterface   $channelContext,
    )
    {
    }

    public function getWishlist(): WishlistInterface
    {
        $customer = $this->customerContext->getCustomer();
        if(!$customer instanceof CustomerInterface){
            throw new WishlistNotFoundException();
        }

        $user = $customer->getUser();
        if (!$user instanceof ShopUserInterface) {
            throw new WishlistNotFoundException();
        }

        $channel = $this->channelContext->getChannel();

        $wishlist = $this->wishlistRepository->findOneByOwnerAndChannel($user, $channel);
        if ($wishlist instanceof WishlistInterface) {
            return $wishlist;
        }

        throw new WishlistNotFoundException();
    }
}
