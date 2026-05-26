<?php

declare(strict_types=1);

namespace Malina141\SyliusWishlistPlugin\Api\Command\Shop\Wishlist;

use Sylius\Bundle\ApiBundle\Attribute\ChannelCodeAware;

#[ChannelCodeAware]
final readonly class CreateWishlist
{
    public function __construct(public string $channelCode)
    {
    }
}
