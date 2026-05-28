<?php

declare(strict_types=1);

namespace Malina141\SyliusWishlistPlugin\Api\Command\Shop\Wishlist;

use Sylius\Bundle\ApiBundle\Attribute\ChannelCodeAware;
use Sylius\Bundle\ApiBundle\Attribute\TokenAware;

#[TokenAware('wishlistToken')]
#[ChannelCodeAware]
final readonly class AddWishlistItemsToCart
{
    public function __construct(
        public string $wishlistToken,
        public array $wishlistItemIds,
        public string $channelCode,
        public string $orderTokenValue,
    ) {
    }
}
