<?php

declare(strict_types=1);

namespace Tests\Malina141\SyliusWishlistPlugin\Behat\Context\Ui\Shop;

use Behat\Behat\Context\Context;
use Behat\Step\Then;
use Behat\Step\When;
use Sylius\Component\Core\Model\ProductInterface;
use Tests\Malina141\SyliusWishlistPlugin\Behat\Page\Shop\Wishlist\IndexPageInterface;
use Webmozart\Assert\Assert;

final readonly class WishlistContext implements Context
{
    public function __construct(
        private IndexPageInterface $indexPage,
    ) {
    }

    #[When('I go to my wishlist page')]
    public function iGoToMyWishlistPage(): void
    {
        $this->indexPage->open(['_locale' => 'en_US']);
    }

    #[When('I remove product :product from my wishlist')]
    public function iRemoveProductFromMyWishlist(ProductInterface $product): void
    {
        $this->indexPage->removeProduct($product->getName());
    }

    #[Then('I should see product :product in my wishlist')]
    public function iShouldSeeProductInMyWishlist(ProductInterface $product): void
    {
        Assert::true(
            $this->indexPage->hasProduct($product->getName()),
            sprintf('Expected to see product "%s" in the wishlist.', $product->getName()),
        );
    }

    #[Then('/^I should see "([^"]+)" as the price of (product "[^"]+") in my wishlist$/')]
    public function iShouldSeeAsThePriceOfProductInMyWishlist(string $price, ProductInterface $product): void
    {
        Assert::same(
            $this->indexPage->getProductPrice($product->getName()),
            $price,
            sprintf('Expected product "%s" to have price "%s" in the wishlist.', $product->getName(), $price),
        );
    }

    #[Then('/^my wishlist should contain (\d+) items?$/')]
    public function myWishlistShouldContainItems(int $expectedCount): void
    {
        Assert::same(
            $this->indexPage->countItems(),
            $expectedCount,
            sprintf('Expected wishlist to contain %d item(s).', $expectedCount),
        );
    }

    #[Then('I should be notified that there are no wishlist items')]
    public function iShouldBeNotifiedThatThereAreNoWishlistItems(): void
    {
        Assert::true(
            $this->indexPage->hasNoResultsMessage(),
            'Expected to see the empty wishlist notification.',
        );
    }
}
