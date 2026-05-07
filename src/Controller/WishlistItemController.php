<?php

declare(strict_types=1);

namespace Malina141\SyliusWishlistPlugin\Controller;

use Malina141\SyliusWishlistPlugin\Adder\WishlistItemsToCartAdderInterface;
use Malina141\SyliusWishlistPlugin\Context\WishlistContextInterface;
use Malina141\SyliusWishlistPlugin\Repository\WishlistItemRepositoryInterface;
use Sylius\Bundle\ResourceBundle\Controller\ResourceController;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Order\Context\CartContextInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

final class WishlistItemController extends ResourceController
{
    public function addItemsToCartAction(
        Request $request,
        WishlistItemsToCartAdderInterface $wishlistItemsToCartAdder,
        WishlistContextInterface $wishlistContext,
        CartContextInterface $cartContext,
        WishlistItemRepositoryInterface $wishlistItemRepository,
    ): Response {
        if (false === $this->isCsrfTokenValid('bulk_wishlist_add_to_cart', $request->request->getString('_csrf_token'))) {
            throw new HttpException(403, 'Invalid CSRF token.');
        }

        /** @var array<string|int> $wishlistItemsIds */
        $wishlistItemsIds = $request->request->all('ids');
        if ([] === $wishlistItemsIds) {
            return $this->redirectToRoute('sylius_shop_cart_summary');
        }

        $wishlist = $wishlistContext->getWishlist();
        /** @var OrderInterface $cart */
        $cart = $cartContext->getCart();

        $wishlistItems = $wishlistItemRepository->findByIdsAndWishlist($wishlistItemsIds, $wishlist);

        $wishlistItemsToCartAdder->add($wishlistItems, $cart);

        return $this->redirectToRoute('sylius_shop_cart_summary');
    }
}
