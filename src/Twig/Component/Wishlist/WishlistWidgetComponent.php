<?php

declare(strict_types=1);

namespace Malina141\SyliusWishlistPlugin\Twig\Component\Wishlist;

use Malina141\SyliusWishlistPlugin\Context\WishlistContextInterface;
use Sylius\Bundle\UiBundle\Twig\Component\TemplatePropTrait;
use Sylius\TwigHooks\LiveComponent\HookableLiveComponentTrait;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveListener;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class WishlistWidgetComponent
{
    use DefaultActionTrait;
    use HookableLiveComponentTrait;
    use TemplatePropTrait;

    public function __construct(
        private readonly WishlistContextInterface $wishlistContext,
    ) {
    }

    #[LiveListener('wishlist_updated')]
    public function wishlistCount(): int
    {
        $wishlist = $this->wishlistContext->getWishlist();

        return $wishlist->getItems()->count();
    }
}
