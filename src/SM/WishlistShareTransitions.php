<?php

declare(strict_types=1);

namespace Malina141\SyliusWishlistPlugin\SM;

interface WishlistShareTransitions
{
    public const string GRAPH = 'sylius_wishlist';

    public const string TRANSITION_SHARE = 'share';

    public const string TRANSITION_UNSHARE = 'unshare';
}
