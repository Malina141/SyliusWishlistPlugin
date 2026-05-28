<?php

declare(strict_types=1);

namespace Malina141\SyliusWishlistPlugin\Api\CommandHandler\Shop\Wishlist;

use Malina141\SyliusWishlistPlugin\Adder\WishlistItemsToCartAdderInterface;
use Malina141\SyliusWishlistPlugin\Api\Command\Shop\Wishlist\AddWishlistItemsToCart;
use Malina141\SyliusWishlistPlugin\Entity\WishlistInterface;
use Malina141\SyliusWishlistPlugin\Repository\WishlistItemRepositoryInterface;
use Malina141\SyliusWishlistPlugin\Repository\WishlistRepositoryInterface;
use Sylius\Bundle\ApiBundle\Context\UserContextInterface;
use Sylius\Component\Channel\Repository\ChannelRepositoryInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\ShopUserInterface;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Webmozart\Assert\Assert;

final readonly class AddWishlistItemsToCartHandler
{
    public function __construct(
        private WishlistRepositoryInterface $wishlistRepository,
        private ChannelRepositoryInterface $channelRepository,
        private WishlistItemRepositoryInterface $wishlistItemRepository,
        private OrderRepositoryInterface $orderRepository,
        private WishlistItemsToCartAdderInterface $wishlistItemsToCartAdder,
        private UserContextInterface $userContext,
    ) {
    }

    public function __invoke(AddWishlistItemsToCart $command): OrderInterface
    {
        $channel = $this->channelRepository->findOneByCode($command->channelCode);
        Assert::isInstanceOf($channel, ChannelInterface::class);

        $wishlist = $this->wishlistRepository->findOneByTokenAndChannel($command->wishlistToken, $channel);
        if (!$wishlist instanceof WishlistInterface) {
            throw new NotFoundHttpException('Wishlist not found');
        }

        $cart = $this->orderRepository->findCartByTokenValueAndChannel($command->orderTokenValue, $channel);
        if (!$cart instanceof OrderInterface) {
            throw new UnprocessableEntityHttpException(\sprintf('Cart with token "%s" was not found', $command->orderTokenValue));
        }

        $this->assertCartAccessible($cart);

        if ([] === $command->wishlistItemIds) {
            return $cart;
        }

        $wishlistItems = $this->wishlistItemRepository->findByIdsAndWishlist($command->wishlistItemIds, $wishlist);
        $this->wishlistItemsToCartAdder->add($wishlistItems, $cart);

        return $cart;
    }

    private function assertCartAccessible(OrderInterface $cart): void
    {
        if ($cart->isCreatedByGuest()) {
            return;
        }

        $cartCustomer = $cart->getCustomer();
        if (!$cartCustomer instanceof CustomerInterface || null === $cartCustomer->getUser()) {
            return;
        }

        $currentUser = $this->userContext->getUser();
        if (
            $currentUser instanceof ShopUserInterface &&
            $currentUser->getCustomer()?->getId() === $cartCustomer->getId()
        ) {
            return;
        }

        throw new NotFoundHttpException('Cart not found.');
    }
}
