<?php

declare(strict_types=1);

namespace Tests\Malina141\SyliusWishlistPlugin\Functional\Api\Shop;

use Malina141\SyliusWishlistPlugin\Entity\WishlistItemInterface;
use Symfony\Component\HttpFoundation\Response;
use Tests\Malina141\SyliusWishlistPlugin\Functional\FunctionalTestCase;

final class WishlistAddToCartTest extends FunctionalTestCase
{
    private array $fixtures;

    protected function setUp(): void
    {
        parent::setUp();

        $this->fixtures = $this->loadFixturesFromFile('Api/WishlistAddToCartTest/wishlist.yaml');
    }

    public function test_guest_adds_selected_wishlist_items_to_cart(): void
    {
        $response = $this->requestJson('POST', '/api/v2/shop/wishlists/api-wishlist-token/add-to-cart', [
            'wishlistItemIds' => [$this->getWishlistItemId('api_wishlist_item_variant')],
            'orderTokenValue' => 'api-cart-token',
        ]);

        $this->assertResponse($response, 'Api/WishlistAddToCartTest/add_items_to_cart_response', Response::HTTP_CREATED);
    }

    public function test_empty_wishlist_item_ids_is_accepted_as_no_op(): void
    {
        $response = $this->requestJson('POST', '/api/v2/shop/wishlists/api-wishlist-token/add-to-cart', [
            'wishlistItemIds' => [],
            'orderTokenValue' => 'api-cart-token',
        ]);

        $this->assertResponse($response, 'Api/WishlistAddToCartTest/empty_selection_response', Response::HTTP_CREATED);
    }

    public function test_unknown_wishlist_token_returns_not_found(): void
    {
        $response = $this->requestJson('POST', '/api/v2/shop/wishlists/unknown-token/add-to-cart', [
            'wishlistItemIds' => [$this->getWishlistItemId('api_wishlist_item_variant')],
            'orderTokenValue' => 'api-cart-token',
        ]);

        $this->assertResponseCode($response, Response::HTTP_NOT_FOUND);
    }

    public function test_invalid_cart_token_returns_unprocessable_entity(): void
    {
        $response = $this->requestJson('POST', '/api/v2/shop/wishlists/api-wishlist-token/add-to-cart', [
            'wishlistItemIds' => [$this->getWishlistItemId('api_wishlist_item_variant')],
            'orderTokenValue' => 'non-existent-cart-token',
        ]);

        $this->assertResponseCode($response, Response::HTTP_NOT_FOUND);
    }

    public function test_cross_channel_cart_token_returns_unprocessable_entity(): void
    {
        $response = $this->requestJson('POST', '/api/v2/shop/wishlists/api-wishlist-token/add-to-cart', [
            'wishlistItemIds' => [$this->getWishlistItemId('api_wishlist_item_variant')],
            'orderTokenValue' => 'api-other-channel-cart-token',
        ]);

        $this->assertResponseCode($response, Response::HTTP_NOT_FOUND);
    }

    public function test_item_ids_from_another_wishlist_are_ignored(): void
    {
        $response = $this->requestJson('POST', '/api/v2/shop/wishlists/api-wishlist-token/add-to-cart', [
            'wishlistItemIds' => [$this->getWishlistItemId('api_other_wishlist_item')],
            'orderTokenValue' => 'api-cart-token',
        ]);

        $this->assertResponse($response, 'Api/WishlistAddToCartTest/empty_selection_response', Response::HTTP_CREATED);
    }

    public function test_adder_validation_skip_behavior_is_preserved(): void
    {
        $response = $this->requestJson('POST', '/api/v2/shop/wishlists/api-wishlist-token/add-to-cart', [
            'wishlistItemIds' => [
                $this->getWishlistItemId('api_wishlist_item_variant'),
                $this->getWishlistItemId('api_wishlist_item_disabled'),
            ],
            'orderTokenValue' => 'api-cart-token',
        ]);

        $this->assertResponse($response, 'Api/WishlistAddToCartTest/add_items_to_cart_response', Response::HTTP_CREATED);
    }

    private function getWishlistItemId(string $reference): int
    {
        $this->assertArrayHasKey($reference, $this->fixtures);
        $wishlistItem = $this->fixtures[$reference];

        $this->assertInstanceOf(WishlistItemInterface::class, $wishlistItem);
        $id = $wishlistItem->getId();

        $this->assertNotNull($id);

        return $id;
    }
}
