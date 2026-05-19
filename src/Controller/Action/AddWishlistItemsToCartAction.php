<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Paweł Jędrzejewski
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Malina141\SyliusWishlistPlugin\Controller\Action;

use Malina141\SyliusWishlistPlugin\Adder\WishlistItemsToCartAdderInterface;
use Malina141\SyliusWishlistPlugin\Context\WishlistContextInterface;
use Malina141\SyliusWishlistPlugin\Repository\WishlistItemRepositoryInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Order\Context\CartContextInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

final readonly class AddWishlistItemsToCartAction
{
    public function __construct(
        private WishlistItemsToCartAdderInterface $wishlistItemsToCartAdder,
        private WishlistContextInterface $wishlistContext,
        private CartContextInterface $cartContext,
        private WishlistItemRepositoryInterface $wishlistItemRepository,
        private CsrfTokenManagerInterface $csrfTokenManager,
        private RouterInterface $router,
        private string $redirectRoute,
        private string $csrfTokenId,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        if (!$this->isCsrfTokenValid($this->csrfTokenId, $request->request->getString('_csrf_token'))) {
            throw new HttpException(Response::HTTP_FORBIDDEN, 'Invalid CSRF token.');
        }

        /** @var array<string|int> $wishlistItemsIds */
        $wishlistItemsIds = $request->request->all('ids');
        if ([] === $wishlistItemsIds) {
            return new RedirectResponse($this->router->generate($this->redirectRoute));
        }

        $wishlist = $this->wishlistContext->getWishlist();
        /** @var OrderInterface $cart */
        $cart = $this->cartContext->getCart();

        $wishlistItems = $this->wishlistItemRepository->findByIdsAndWishlist($wishlistItemsIds, $wishlist);

        $this->wishlistItemsToCartAdder->add($wishlistItems, $cart);

        return new RedirectResponse($this->router->generate($this->redirectRoute));
    }

    private function isCsrfTokenValid(string $id, ?string $token): bool
    {
        return $this->csrfTokenManager->isTokenValid(new CsrfToken($id, $token));
    }
}
