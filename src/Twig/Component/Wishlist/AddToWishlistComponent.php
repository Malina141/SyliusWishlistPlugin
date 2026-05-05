<?php

declare(strict_types=1);

namespace Malina141\SyliusWishlistPlugin\Twig\Component\Wishlist;

use Doctrine\ORM\EntityManagerInterface;
use Malina141\SyliusWishlistPlugin\Context\WishlistContextInterface;
use Malina141\SyliusWishlistPlugin\Modifier\WishlistModifierInterface;
use Sylius\Bundle\ShopBundle\Twig\Component\Product\AddToCartFormComponent;
use Sylius\Bundle\ShopBundle\Twig\Component\Product\Trait\ProductLivePropTrait;
use Sylius\Bundle\ShopBundle\Twig\Component\Product\Trait\ProductVariantLivePropTrait;
use Sylius\Bundle\UiBundle\Twig\Component\TemplatePropTrait;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Core\Repository\ProductRepositoryInterface;
use Sylius\Component\Core\Repository\ProductVariantRepositoryInterface;
use Sylius\TwigHooks\LiveComponent\HookableLiveComponentTrait;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveListener;
use Symfony\UX\LiveComponent\ComponentToolsTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class AddToWishlistComponent
{
    use DefaultActionTrait;
    use HookableLiveComponentTrait;
    use TemplatePropTrait;
    use ProductLivePropTrait;
    use ProductVariantLivePropTrait;
    use ComponentToolsTrait;

    /**
     * @param ProductRepositoryInterface<ProductInterface> $productRepository
     * @param ProductVariantRepositoryInterface<ProductVariantInterface> $productVariantRepository
     */
    public function __construct(
        private readonly WishlistContextInterface $wishlistContext,
        private readonly WishlistModifierInterface $wishlistModifier,
        private readonly EntityManagerInterface $wishlistManager,
        ProductRepositoryInterface $productRepository,
        ProductVariantRepositoryInterface $productVariantRepository,
    ) {
        $this->initializeProduct($productRepository);
        $this->initializeProductVariant($productVariantRepository);
    }

    #[LiveListener(AddToCartFormComponent::SYLIUS_SHOP_VARIANT_CHANGED)]
    public function updateVariant(#[LiveArg] mixed $variantId): void
    {
        $this->variant = $this->hydrateProductVariant($variantId);
    }

    #[LiveAction]
    public function add(): void
    {
        $variant = $this->resolveVariant();
        if (!$variant instanceof ProductVariantInterface) {
            return;
        }

        $wishlist = $this->wishlistContext->getWishlist();

        $this->wishlistModifier->addVariant($wishlist, $variant);

        $this->wishlistManager->persist($wishlist);
        $this->wishlistManager->flush();

        $this->emit('wishlist_updated');
    }

    #[LiveAction]
    public function remove(): void
    {
        $variant = $this->resolveVariant();
        if (!$variant instanceof ProductVariantInterface) {
            return;
        }

        $wishlist = $this->wishlistContext->getWishlist();

        $this->wishlistModifier->removeVariant($wishlist, $variant);

        $this->wishlistManager->persist($wishlist);
        $this->wishlistManager->flush();

        $this->emit('wishlist_updated');
    }

    public function isInWishlist(): bool
    {
        $variant = $this->resolveVariant();
        if (!$variant instanceof ProductVariantInterface) {
            return false;
        }

        $wishlist = $this->wishlistContext->getWishlist();

        return $wishlist->hasProductVariant($variant);
    }

    private function resolveVariant(): ?ProductVariantInterface
    {
        $variant = $this->variant ?? $this->product?->getEnabledVariants()->first();

        return $variant instanceof ProductVariantInterface ? $variant : null;
    }
}
