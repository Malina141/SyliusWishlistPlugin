<?php

declare(strict_types=1);

namespace Malina141\SyliusWishlistPlugin\Api\Command\Shop\Wishlist;

use Sylius\Bundle\ApiBundle\Attribute\ChannelCodeAware;
use Sylius\Bundle\ApiBundle\Attribute\TokenAware;

#[TokenAware('wishlistToken')]
#[ChannelCodeAware]
final readonly class AddItemToWishlist
{
    public function __construct(
        public string $wishlistToken,
        public string $productVariantCode,
        public string $channelCode,
    ) {
    }
}
