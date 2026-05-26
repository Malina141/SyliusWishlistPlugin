<?php

declare(strict_types=1);

namespace Tests\Malina141\SyliusWishlistPlugin\Unit\Controller\Action;

use Malina141\SyliusWishlistPlugin\Adder\WishlistItemsToCartAdderInterface;
use Malina141\SyliusWishlistPlugin\Context\WishlistContextInterface;
use Malina141\SyliusWishlistPlugin\Controller\Action\AddWishlistItemsToCartAction;
use Malina141\SyliusWishlistPlugin\Entity\WishlistInterface;
use Malina141\SyliusWishlistPlugin\Entity\WishlistItemInterface;
use Malina141\SyliusWishlistPlugin\Repository\WishlistItemRepositoryInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Order\Context\CartContextInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

#[CoversClass(AddWishlistItemsToCartAction::class)]
final class AddWishlistItemsToCartActionTest extends TestCase
{
    private WishlistItemsToCartAdderInterface&MockObject $wishlistItemsToCartAdder;

    private WishlistContextInterface&MockObject $wishlistContext;

    private CartContextInterface&MockObject $cartContext;

    private WishlistItemRepositoryInterface&MockObject $wishlistItemRepository;

    private CsrfTokenManagerInterface&MockObject $csrfTokenManager;

    private RouterInterface&MockObject $router;

    private AddWishlistItemsToCartAction $action;

    protected function setUp(): void
    {
        $this->wishlistItemsToCartAdder = $this->createMock(WishlistItemsToCartAdderInterface::class);
        $this->wishlistContext = $this->createMock(WishlistContextInterface::class);
        $this->cartContext = $this->createMock(CartContextInterface::class);
        $this->wishlistItemRepository = $this->createMock(WishlistItemRepositoryInterface::class);
        $this->csrfTokenManager = $this->createMock(CsrfTokenManagerInterface::class);
        $this->router = $this->createMock(RouterInterface::class);

        $this->action = new AddWishlistItemsToCartAction(
            $this->wishlistItemsToCartAdder,
            $this->wishlistContext,
            $this->cartContext,
            $this->wishlistItemRepository,
            $this->csrfTokenManager,
            $this->router,
            'wishlist_index',
            'bulk_add_to_cart',
        );
    }

    #[Test]
    public function it_redirects_without_touching_wishlist_when_no_items_are_selected(): void
    {
        $request = new Request([], ['_csrf_token' => 'valid-token', 'ids' => []]);

        $this->csrfTokenManager->expects($this->once())->method('isTokenValid')->willReturn(true);
        $this->wishlistContext->expects($this->never())->method('getWishlist');
        $this->cartContext->expects($this->never())->method('getCart');
        $this->wishlistItemRepository->expects($this->never())->method('findByIdsAndWishlist');
        $this->wishlistItemsToCartAdder->expects($this->never())->method('add');
        $this->router->expects($this->once())->method('generate')->with('wishlist_index')->willReturn('/wishlist');

        $response = ($this->action)($request);

        $this->assertSame(Response::HTTP_FOUND, $response->getStatusCode());
        $this->assertSame('/wishlist', $response->headers->get('Location'));
    }

    #[Test]
    public function it_adds_only_selected_items_from_current_wishlist_to_current_cart(): void
    {
        $request = new Request([], ['_csrf_token' => 'valid-token', 'ids' => ['1', 2]]);
        $wishlist = $this->createMock(WishlistInterface::class);
        $cart = $this->createMock(OrderInterface::class);
        $firstWishlistItem = $this->createMock(WishlistItemInterface::class);
        $secondWishlistItem = $this->createMock(WishlistItemInterface::class);

        $this->csrfTokenManager->expects($this->once())->method('isTokenValid')->willReturn(true);
        $this->wishlistContext->expects($this->once())->method('getWishlist')->willReturn($wishlist);
        $this->cartContext->expects($this->once())->method('getCart')->willReturn($cart);
        $this->wishlistItemRepository
            ->expects($this->once())
            ->method('findByIdsAndWishlist')
            ->with(['1', 2], $wishlist)
            ->willReturn([$firstWishlistItem, $secondWishlistItem])
        ;
        $this->wishlistItemsToCartAdder
            ->expects($this->once())
            ->method('add')
            ->with([$firstWishlistItem, $secondWishlistItem], $cart)
        ;
        $this->router->expects($this->once())->method('generate')->with('wishlist_index')->willReturn('/wishlist');

        $response = ($this->action)($request);

        $this->assertSame(Response::HTTP_FOUND, $response->getStatusCode());
        $this->assertSame('/wishlist', $response->headers->get('Location'));
    }
}
