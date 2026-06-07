<?php

declare(strict_types=1);

namespace Tests\Malina141\SyliusWishlistPlugin\Behat\Context\Ui\Shop;

use Behat\Behat\Context\Context;
use Behat\MinkExtension\Context\RawMinkContext;
use Behat\Step\Then;
use Behat\Step\When;
use Sylius\Behat\NotificationType;
use Sylius\Behat\Page\Shop\Cart\SummaryPageInterface;
use Sylius\Behat\Page\Shop\HomePageInterface;
use Sylius\Behat\Service\NotificationCheckerInterface;
use Sylius\Behat\Service\SharedStorageInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Tests\Malina141\SyliusWishlistPlugin\Behat\Element\Shop\ProductWishlistElementInterface;
use Tests\Malina141\SyliusWishlistPlugin\Behat\Element\Shop\WishlistWidgetElementInterface;
use Tests\Malina141\SyliusWishlistPlugin\Behat\Page\Shop\Wishlist\IndexPageInterface;
use Tests\Malina141\SyliusWishlistPlugin\Behat\Page\Shop\Wishlist\SharedShowPageInterface;
use Webmozart\Assert\Assert;

final class WishlistContext extends RawMinkContext implements Context
{
    public function __construct(
        private readonly IndexPageInterface $indexPage,
        private readonly SharedShowPageInterface $sharedShowPage,
        private readonly WishlistWidgetElementInterface $wishlistWidgetElement,
        private readonly ProductWishlistElementInterface $productWishlistElement,
        private readonly SharedStorageInterface $sharedStorage,
        private readonly HomePageInterface $homePage,
        private readonly SummaryPageInterface $cartSummaryPage,
        private readonly NotificationCheckerInterface $notificationChecker,
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
        Assert::notNull($product->getName());
        $this->indexPage->removeProduct($product->getName());
    }

    #[When('I add this product to my wishlist')]
    public function iAddThisProductToMyWishlist(): void
    {
        $this->productWishlistElement->addCurrentProduct();
    }

    #[When('I remove this product from my wishlist')]
    public function iRemoveThisProductFromMyWishlist(): void
    {
        $this->productWishlistElement->removeCurrentProduct();
    }

    #[When('I add product :product to my wishlist from the product list')]
    public function iAddProductToMyWishlistFromTheProductList(ProductInterface $product): void
    {
        Assert::notNull($product->getCode());

        $this->productWishlistElement->addProductFromList($product->getCode());
    }

    #[When('I remove product :product from my wishlist from the product list')]
    public function iRemoveProductFromMyWishlistFromTheProductList(ProductInterface $product): void
    {
        Assert::notNull($product->getCode());

        $this->productWishlistElement->removeProductFromList($product->getCode());
    }

    #[When('I go back to the homepage')]
    public function iGoBackToTheHomepage(): void
    {
        $this->homePage->open();
    }

    #[When('I select product :product in my wishlist')]
    public function iSelectProductInMyWishlist(ProductInterface $product): void
    {
        Assert::notNull($product->getName());
        $this->indexPage->selectProduct($product->getName());
    }

    #[When('I select all wishlist products')]
    public function iSelectAllWishlistProducts(): void
    {
        $this->indexPage->selectAllProducts();
    }

    #[When('I bulk delete selected wishlist products')]
    public function iBulkDeleteSelectedWishlistProducts(): void
    {
        $this->indexPage->bulkDeleteSelectedProducts();
    }

    #[When('I add selected wishlist products to my cart')]
    public function iAddSelectedWishlistProductsToMyCart(): void
    {
        $this->indexPage->bulkAddSelectedProductsToCart();
    }

    #[When('I rename my wishlist to :name')]
    public function iRenameMyWishlistTo(string $name): void
    {
        $this->indexPage->renameWishlist($name);
    }

    #[When('I reload my wishlist page')]
    public function iReloadMyWishlistPage(): void
    {
        $this->indexPage->reload();
    }

    #[When('I share my wishlist')]
    public function iShareMyWishlist(): void
    {
        $this->indexPage->shareWishlist();
        $this->rememberPublicShareTokenFromWishlistPage();
    }

    #[When('I unshare my wishlist')]
    public function iUnshareMyWishlist(): void
    {
        if ($this->indexPage->hasPublicWishlistLink()) {
            $this->rememberPublicShareTokenFromWishlistPage();
        }

        $this->indexPage->unshareWishlist();
    }

    #[When('another visitor opens my public wishlist link')]
    public function anotherVisitorOpensMyPublicWishlistLink(): void
    {
        $this->openSharedWishlistPage('wishlist_public_share_token');
    }

    #[When('another visitor opens the previously public wishlist link')]
    public function anotherVisitorOpensThePreviouslyPublicWishlistLink(): void
    {
        $this->tryToOpenSharedWishlistPage('wishlist_previously_public_share_token');
    }

    #[When('I follow the wishlist widget in the shop header')]
    public function iFollowTheWishlistWidgetInTheShopHeader(): void
    {
        $this->homePage->open();
        $this->wishlistWidgetElement->follow();
    }

    #[Then('I should see product :product in my wishlist')]
    public function iShouldSeeProductInMyWishlist(ProductInterface $product): void
    {
        Assert::notNull($product->getName());
        Assert::true(
            $this->indexPage->hasProduct($product->getName()),
            sprintf('Expected to see product "%s" in the wishlist.', $product->getName()),
        );
    }

    #[Then('I should not see product :product in my wishlist')]
    public function iShouldNotSeeProductInMyWishlist(ProductInterface $product): void
    {
        Assert::notNull($product->getName());
        Assert::false(
            $this->indexPage->hasProduct($product->getName()),
            sprintf('Expected not to see product "%s" in the wishlist.', $product->getName()),
        );
    }

    #[Then('/^I should see "([^"]+)" as the price of (product "[^"]+") in my wishlist$/')]
    public function iShouldSeeAsThePriceOfProductInMyWishlist(string $price, ProductInterface $product): void
    {
        Assert::notNull($product->getName());
        Assert::same(
            $this->indexPage->getProductPrice($product->getName()),
            $price,
            sprintf('Expected product "%s" to have price "%s" in the wishlist.', $product->getName(), $price),
        );
    }

    #[Then('/^I should see "([^"]+)" as an option of (product "[^"]+") in my wishlist$/')]
    public function iShouldSeeAsAnOptionOfProductInMyWishlist(string $option, ProductInterface $product): void
    {
        Assert::notNull($product->getName());
        Assert::inArray(
            $option,
            $this->indexPage->getProductOptionTexts($product->getName()),
            sprintf('Expected product "%s" to have option "%s" in the wishlist.', $product->getName(), $option),
        );
    }

    #[Then('my cart should contain product :product')]
    public function myCartShouldContainProduct(ProductInterface $product): void
    {
        Assert::notNull($product->getName());
        $this->cartSummaryPage->open();

        Assert::true(
            $this->cartSummaryPage->hasItemNamed($product->getName()),
            sprintf('Expected cart to contain product "%s".', $product->getName()),
        );
    }

    #[Then('my cart should not contain product :product')]
    public function myCartShouldNotContainProduct(ProductInterface $product): void
    {
        Assert::notNull($product->getName());
        $this->cartSummaryPage->open();

        Assert::false(
            $this->cartSummaryPage->hasItemNamed($product->getName()),
            sprintf('Expected cart not to contain product "%s".', $product->getName()),
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

    #[Then('/^my wishlist widget should show (\d+) items?$/')]
    public function myWishlistWidgetShouldShowItems(int $expectedCount): void
    {
        $isExpectedCountVisible = $this->getSession()->getPage()->waitFor(
            5,
            fn (): bool => $this->wishlistWidgetElement->getWishlistCount() === $expectedCount,
        );

        if (true !== $isExpectedCountVisible) {
            throw new \RuntimeException(sprintf(
                'Expected wishlist widget to show %d item(s), but it shows %d.',
                $expectedCount,
                $this->wishlistWidgetElement->getWishlistCount(),
            ));
        }
    }

    #[Then('the wishlist name should be displayed as the default wishlist name')]
    public function theWishlistNameShouldBeDisplayedAsTheDefaultWishlistName(): void
    {
        Assert::same($this->indexPage->getWishlistName(), "\u{2014}");
    }

    #[Then('the wishlist name should be :name')]
    public function theWishlistNameShouldBe(string $name): void
    {
        Assert::same($this->indexPage->getWishlistName(), $name);
    }

    #[Then('I should be notified that the wishlist name has been saved')]
    public function iShouldBeNotifiedThatTheWishlistNameHasBeenSaved(): void
    {
        Assert::true($this->indexPage->hasNameSavedMessage());
    }

    #[Then('I should be notified that the wishlist has been successfully updated')]
    public function iShouldBeNotifiedThatTheWishlistHasBeenSuccessfullyUpdated(): void
    {
        $this->notificationChecker->checkNotification('Wishlist has been successfully updated.', NotificationType::success());
    }

    #[Then('my wishlist should be marked as shared')]
    public function myWishlistShouldBeMarkedAsShared(): void
    {
        Assert::true($this->indexPage->isWishlistMarkedAsShared());
    }

    #[Then('my wishlist should be marked as private')]
    public function myWishlistShouldBeMarkedAsPrivate(): void
    {
        Assert::false($this->indexPage->isWishlistMarkedAsShared());
    }

    #[Then('I should see a public wishlist link')]
    public function iShouldSeeAPublicWishlistLink(): void
    {
        Assert::true($this->indexPage->hasPublicWishlistLink());
    }

    #[Then('I should not see a public wishlist link')]
    public function iShouldNotSeeAPublicWishlistLink(): void
    {
        Assert::false($this->indexPage->hasPublicWishlistLink());
    }

    #[Then('I should be able to copy the public wishlist link')]
    public function iShouldBeAbleToCopyThePublicWishlistLink(): void
    {
        Assert::true($this->indexPage->hasPublicWishlistLink());
        $this->indexPage->copyPublicWishlistLink();
    }

    #[Then('they should see a shared wishlist page')]
    public function theyShouldSeeASharedWishlistPage(): void
    {
        Assert::true($this->sharedShowPage->hasSharedWishlistPage());
    }

    #[Then('they should see product :product in the shared wishlist')]
    public function theyShouldSeeProductInTheSharedWishlist(ProductInterface $product): void
    {
        Assert::notNull($product->getName());
        Assert::true($this->sharedShowPage->hasProduct($product->getName()));
    }

    #[Then('/^they should see "([^"]+)" as the price of (product "[^"]+") in the shared wishlist$/')]
    public function theyShouldSeeAsThePriceOfProductInTheSharedWishlist(string $price, ProductInterface $product): void
    {
        Assert::notNull($product->getName());
        Assert::same($this->sharedShowPage->getProductPrice($product->getName()), $price);
    }

    #[Then('they should not be able to see my shared wishlist')]
    public function theyShouldNotBeAbleToSeeMySharedWishlist(): void
    {
        Assert::true($this->sharedShowPage->isAccessDenied());
    }

    #[Then('I should be on my wishlist page')]
    public function iShouldBeOnMyWishlistPage(): void
    {
        $this->indexPage->verify();
    }

    #[Then('I should be able to remove this product from my wishlist')]
    public function iShouldBeAbleToRemoveThisProductFromMyWishlist(): void
    {
        Assert::true(
            $this->productWishlistElement->hasCurrentProductRemoveButton(),
            'Expected the current product wishlist control to switch to remove mode.',
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

    private function rememberPublicShareTokenFromWishlistPage(): void
    {
        $path = parse_url($this->indexPage->getPublicWishlistLink(), \PHP_URL_PATH);
        Assert::string($path);

        $token = basename($path);
        $this->sharedStorage->set('wishlist_public_share_token', $token);
        $this->sharedStorage->set('wishlist_previously_public_share_token', $token);
    }

    private function openSharedWishlistPage(string $storageKey): void
    {
        Assert::true($this->sharedStorage->has($storageKey), sprintf('Expected "%s" to be stored.', $storageKey));

        $this->sharedShowPage->open([
            '_locale' => 'en_US',
            'token' => $this->sharedStorage->get($storageKey),
        ]);
    }

    private function tryToOpenSharedWishlistPage(string $storageKey): void
    {
        Assert::true($this->sharedStorage->has($storageKey), sprintf('Expected "%s" to be stored.', $storageKey));

        $this->sharedShowPage->tryToOpen([
            '_locale' => 'en_US',
            'token' => $this->sharedStorage->get($storageKey),
        ]);
    }
}
