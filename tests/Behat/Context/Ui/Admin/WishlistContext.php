<?php

declare(strict_types=1);

namespace Tests\Malina141\SyliusWishlistPlugin\Behat\Context\Ui\Admin;

use Behat\Behat\Context\Context;
use Behat\Step\Then;
use Behat\Step\When;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Product\Resolver\ProductVariantResolverInterface;
use Tests\Malina141\SyliusWishlistPlugin\Behat\Page\Admin\Wishlist\IndexPageInterface;
use Tests\Malina141\SyliusWishlistPlugin\Behat\Page\Admin\Wishlist\ShowPageInterface;
use Webmozart\Assert\Assert;

final readonly class WishlistContext implements Context
{
    public function __construct(
        private IndexPageInterface $indexPage,
        private ShowPageInterface $showPage,
        private ProductVariantResolverInterface $productVariantResolver,
    ) {
    }

    #[When('I browse wishlists in the admin panel')]
    public function iBrowseWishlistsInTheAdminPanel(): void
    {
        $this->indexPage->open();
    }

    #[When('I search admin wishlists for :phrase')]
    public function iSearchAdminWishlistsFor(string $phrase): void
    {
        $this->indexPage->search($phrase);
    }

    #[When('I filter admin wishlists by the :channel channel')]
    public function iFilterAdminWishlistsByTheChannel(ChannelInterface $channel): void
    {
        $this->indexPage->filterByChannel($channel);
    }

    #[When('I filter admin wishlists to registered customers')]
    public function iFilterAdminWishlistsToRegisteredCustomers(): void
    {
        $this->indexPage->filterByRegisteredCustomers();
    }

    #[When('I filter admin wishlists to guests')]
    public function iFilterAdminWishlistsToGuests(): void
    {
        $this->indexPage->filterByGuests();
    }

    #[When('I view wishlist :name in the admin panel')]
    public function iViewWishlistInTheAdminPanel(string $name): void
    {
        $this->indexPage->open();
        $this->indexPage->openWishlistDetails($name);
    }

    #[Then('I should see wishlist :name in the admin wishlist list')]
    public function iShouldSeeWishlistInTheAdminWishlistList(string $name): void
    {
        Assert::true($this->indexPage->hasWishlistNamed($name), sprintf('Expected wishlist "%s" to be visible in the admin list.', $name));
    }

    #[Then('I should not see wishlist :name in the admin wishlist list')]
    public function iShouldNotSeeWishlistInTheAdminWishlistList(string $name): void
    {
        Assert::false($this->indexPage->hasWishlistNamed($name), sprintf('Expected wishlist "%s" not to be visible in the admin list.', $name));
    }

    #[Then('I should see owner :owner for wishlist :name')]
    public function iShouldSeeOwnerForWishlist(string $owner, string $name): void
    {
        Assert::contains($this->indexPage->getOwnerForWishlist($name), $owner);
    }

    #[Then('I should see :count as the number of items for wishlist :name')]
    public function iShouldSeeAsTheNumberOfItemsForWishlist(string $count, string $name): void
    {
        Assert::same($this->indexPage->getItemsCountForWishlist($name), $count);
    }

    #[Then('I should see wishlist name :name in the admin wishlist details')]
    public function iShouldSeeWishlistNameInTheAdminWishlistDetails(string $name): void
    {
        Assert::true($this->showPage->hasWishlistName($name));
    }

    #[Then('I should see owner :owner in the admin wishlist details')]
    public function iShouldSeeOwnerInTheAdminWishlistDetails(string $owner): void
    {
        Assert::true($this->showPage->hasOwner($owner));
    }

    #[Then('I should see the :channel channel in the admin wishlist details')]
    public function iShouldSeeTheChannelInTheAdminWishlistDetails(ChannelInterface $channel): void
    {
        Assert::notNull($channel->getName());
        Assert::true($this->showPage->hasChannel($channel->getName()));
    }

    #[Then('I should see product :product in the admin wishlist details')]
    public function iShouldSeeProductInTheAdminWishlistDetails(ProductInterface $product): void
    {
        Assert::notNull($product->getName());
        Assert::true($this->showPage->hasProduct($product->getName()));
    }

    #[Then('I should see the variant code of product :product in the admin wishlist details')]
    public function iShouldSeeTheVariantCodeOfProductInTheAdminWishlistDetails(ProductInterface $product): void
    {
        $variant = $this->productVariantResolver->getVariant($product);

        Assert::isInstanceOf($variant, ProductVariantInterface::class);
        Assert::notNull($variant->getCode());
        Assert::true($this->showPage->hasVariantCode($variant->getCode()));
    }

    #[Then('I should be notified that the wishlist has no items in the admin wishlist details')]
    public function iShouldBeNotifiedThatTheWishlistHasNoItemsInTheAdminWishlistDetails(): void
    {
        Assert::true($this->showPage->hasNoItemsMessage());
    }
}
