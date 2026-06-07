<?php

declare(strict_types=1);

namespace Malina141\SyliusWishlistPlugin\Twig\Component\Wishlist;

use Malina141\SyliusWishlistPlugin\Context\WishlistContextInterface;
use Sylius\Bundle\UiBundle\Twig\Component\TemplatePropTrait;
use Sylius\TwigHooks\LiveComponent\HookableLiveComponentTrait;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveListener;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\TwigComponent\Attribute\PreMount;

#[AsLiveComponent]
final class WishlistWidgetComponent
{
    use DefaultActionTrait;
    use HookableLiveComponentTrait;
    use TemplatePropTrait;

    #[LiveProp]
    public int $wishlistCount = 0;

    public function __construct(
        private readonly WishlistContextInterface $wishlistContext,
    ) {
    }

    #[PreMount]
    public function initializeWishlistCount(): void
    {
        $this->wishlistCount = $this->countWishlistItems();
    }

    #[LiveListener('wishlist_updated')]
    public function refreshWishlistCount(): void
    {
        $this->wishlistCount = $this->countWishlistItems();
    }

    private function countWishlistItems(): int
    {
        $wishlist = $this->wishlistContext->getWishlist();

        return $wishlist->getItems()->count();
    }
}
