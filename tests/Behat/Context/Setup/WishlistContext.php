<?php

declare(strict_types=1);

namespace Tests\Malina141\SyliusWishlistPlugin\Behat\Context\Setup;

use Behat\Behat\Context\Context;
use Behat\Step\Given;
use Doctrine\ORM\EntityManagerInterface;
use Malina141\SyliusWishlistPlugin\Entity\WishlistInterface;
use Malina141\SyliusWishlistPlugin\Modifier\WishlistModifierInterface;
use Malina141\SyliusWishlistPlugin\Provider\WishlistShareTokenProviderInterface;
use Malina141\SyliusWishlistPlugin\SM\WishlistShareStates;
use Sylius\Behat\Service\Setter\CookieSetterInterface;
use Sylius\Behat\Service\SharedStorageInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Core\Model\ShopUserInterface;
use Sylius\Component\Core\Repository\CustomerRepositoryInterface;
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
        private WishlistShareTokenProviderInterface $wishlistShareTokenProvider,
        private CustomerRepositoryInterface $customerRepository,
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

    #[Given('my wishlist contains :productVariant')]
    #[Given('my wishlist contains the :productVariant')]
    #[Given('/^my wishlist contains the ("[^"]+" variant of product "[^"]+")$/')]
    public function myWishlistContainsVariant(ProductVariantInterface $productVariant): void
    {
        $wishlist = $this->getOrCreateWishlist();

        $this->wishlistModifier->addVariant($wishlist, $productVariant);

        $this->wishlistManager->persist($wishlist);
        $this->wishlistManager->flush();
    }

    #[Given('my wishlist is shared')]
    public function myWishlistIsShared(): void
    {
        $wishlist = $this->getOrCreateWishlist();
        $shareToken = $this->wishlistShareTokenProvider->provideShareToken($wishlist);

        $wishlist->setShareState(WishlistShareStates::STATE_SHARED);

        $this->wishlistManager->persist($wishlist);
        $this->wishlistManager->flush();
        $this->sharedStorage->set('wishlist_public_share_token', $shareToken);
        $this->sharedStorage->set('wishlist_previously_public_share_token', $shareToken);
    }

    #[Given('there is a wishlist named :name')]
    public function thereIsAWishlistNamed(string $name): void
    {
        $this->createWishlist($name, $this->getChannel());
    }

    #[Given('there is a wishlist named :name on the :channel channel')]
    public function thereIsAWishlistNamedOnTheChannel(string $name, ChannelInterface $channel): void
    {
        $this->createWishlist($name, $channel);
    }

    #[Given('there is a guest wishlist named :name')]
    public function thereIsAGuestWishlistNamed(string $name): void
    {
        $this->createWishlist($name, $this->getChannel());
    }

    #[Given('there is a guest wishlist named :name containing product :product')]
    public function thereIsAGuestWishlistNamedContainingProduct(string $name, ProductInterface $product): void
    {
        $wishlist = $this->createWishlist($name, $this->getChannel());

        $this->addProductToWishlist($wishlist, $product);
    }

    #[Given('there is a customer wishlist named :name owned by :email')]
    public function thereIsACustomerWishlistNamedOwnedBy(string $name, string $email): void
    {
        $this->createWishlist($name, $this->getChannel(), $this->getCustomer($email));
    }

    #[Given('customer :email has a wishlist named :name containing product :product')]
    public function customerHasAWishlistNamedContainingProduct(string $email, string $name, ProductInterface $product): void
    {
        $wishlist = $this->createWishlist($name, $this->getChannel(), $this->getCustomer($email));

        $this->addProductToWishlist($wishlist, $product);
    }

    private function getOrCreateWishlist(): WishlistInterface
    {
        if ($this->sharedStorage->has('wishlist')) {
            return $this->sharedStorage->get('wishlist');
        }

        /** @var WishlistInterface $wishlist */
        $wishlist = $this->wishlistFactory->createNew();
        $wishlist->setChannel($this->getChannel());

        $token = bin2hex(random_bytes(16));
        $wishlist->setToken($token);
        $this->cookieSetter->setCookie($this->wishlistCookieName, $token);

        $this->sharedStorage->set('wishlist', $wishlist);

        return $wishlist;
    }

    private function createWishlist(string $name, ChannelInterface $channel, ?CustomerInterface $customer = null): WishlistInterface
    {
        /** @var WishlistInterface $wishlist */
        $wishlist = $this->wishlistFactory->createNew();
        $wishlist->setName($name);
        $wishlist->setChannel($channel);
        $wishlist->setToken(bin2hex(random_bytes(16)));

        if (null !== $customer) {
            $user = $customer->getUser();

            Assert::isInstanceOf($user, ShopUserInterface::class);

            $wishlist->setOwner($user);
        }

        $this->wishlistManager->persist($wishlist);
        $this->wishlistManager->flush();

        $this->sharedStorage->set('wishlist', $wishlist);

        return $wishlist;
    }

    private function addProductToWishlist(WishlistInterface $wishlist, ProductInterface $product): void
    {
        $variant = $this->productVariantResolver->getVariant($product);

        Assert::isInstanceOf($variant, ProductVariantInterface::class);

        $this->wishlistModifier->addVariant($wishlist, $variant);

        $this->wishlistManager->persist($wishlist);
        $this->wishlistManager->flush();
    }

    private function getCustomer(string $email): CustomerInterface
    {
        $customer = $this->customerRepository->findOneBy(['email' => $email]);

        Assert::isInstanceOf($customer, CustomerInterface::class);

        return $customer;
    }

    private function getChannel(): ChannelInterface
    {
        $channel = $this->sharedStorage->get('channel');

        Assert::isInstanceOf($channel, ChannelInterface::class);

        return $channel;
    }
}
