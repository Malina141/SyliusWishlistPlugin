<?php

declare(strict_types=1);

namespace Malina141\SyliusWishlistPlugin\Context;

use Malina141\SyliusWishlistPlugin\Entity\WishlistInterface;
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
        private FactoryInterface         $wishlistFactory,
        private CustomerContextInterface $customerContext,
    )
    {
    }

    public function getWishlist(): WishlistInterface
    {
        /** @var WishlistInterface $wishlist */
        $wishlist = $this->wishlistFactory->createNew();

        $customer = $this->customerContext->getCustomer();
        if(!$customer instanceof CustomerInterface){
            return $wishlist;
        }

        $user = $customer->getUser();

        if ($user instanceof ShopUserInterface) {
            $wishlist->setOwner($user);
            return $wishlist;
        }

        return $wishlist;
    }
}
