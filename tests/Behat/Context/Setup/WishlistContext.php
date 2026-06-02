<?php

declare(strict_types=1);

namespace Tests\Malina141\SyliusWishlistPlugin\Behat\Context\Setup;

use Behat\Behat\Context\Context;
use Behat\Step\Given;
use Doctrine\ORM\EntityManagerInterface;
use Malina141\SyliusWishlistPlugin\Entity\WishlistInterface;
use Malina141\SyliusWishlistPlugin\Modifier\WishlistModifierInterface;
use Sylius\Behat\Service\Setter\CookieSetterInterface;
use Sylius\Behat\Service\SharedStorageInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Product\Resolver\ProductVariantResolverInterface;
use Sylius\Resource\Factory\FactoryInterface;
use Webmozart\Assert\Assert;

final readonly class WishlistContext implements Context
{
    public function __construct(
        private SharedStorageInterface $sharedStorage,
        private FactoryInterface $wishlistFactory,
        private WishlistModifierInterface $wishlistModifier,
        private ProductVariantResolverInterface $productVariantResolver,
        private EntityManagerInterface $wishlistManager,
        private CookieSetterInterface $cookieSetter,
        private string $wishlistCookieName,
    ) {
    }

    #[Given('my wishlist contains product :product')]
    public function myWishlistContainsProduct(ProductInterface $product): void
    {
        $wishlist = $this->getOrCreateWishlist();
        $variant = $this->productVariantResolver->getVariant($product);

        Assert::isInstanceOf($variant, ProductVariantInterface::class);

        $this->wishlistModifier->addVariant($wishlist, $variant);

        $this->wishlistManager->persist($wishlist);
        $this->wishlistManager->flush();
    }

    private function getOrCreateWishlist(): WishlistInterface
    {
        if ($this->sharedStorage->has('wishlist')) {
            return $this->sharedStorage->get('wishlist');
        }

        $wishlist = $this->wishlistFactory->createNew();
        $wishlist->setChannel($this->getChannel());

        $token = bin2hex(random_bytes(16));
        $wishlist->setToken($token);
        $this->cookieSetter->setCookie($this->wishlistCookieName, $token);

        $this->sharedStorage->set('wishlist', $wishlist);

        return $wishlist;
    }

    private function getChannel(): ChannelInterface
    {
        $channel = $this->sharedStorage->get('channel');

        Assert::isInstanceOf($channel, ChannelInterface::class);

        return $channel;
    }
}
