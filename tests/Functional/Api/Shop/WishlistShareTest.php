<?php

declare(strict_types=1);

namespace Tests\Malina141\SyliusWishlistPlugin\Functional\Api\Shop;

use Symfony\Component\HttpFoundation\Response;
use Tests\Malina141\SyliusWishlistPlugin\Functional\FunctionalTestCase;

final class WishlistShareTest extends FunctionalTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadFixturesFromFile('Api/WishlistShareTest/wishlist.yaml');
    }

    public function test_guest_can_share_wishlist_and_receives_share_token(): void
    {
        $response = $this->requestJson('PATCH', '/api/v2/shop/wishlists/unshared-token/share');

        $this->assertResponse($response, 'Api/WishlistShareTest/share_wishlist_response', Response::HTTP_OK);
    }

    public function test_guest_can_read_shared_wishlist_by_share_token(): void
    {
        $response = $this->requestJson('GET', '/api/v2/shop/shared-wishlists/public-share-token-abc');

        $this->assertResponse($response, 'Api/WishlistShareTest/shared_wishlist_read_response', Response::HTTP_OK);
    }

    public function test_unshared_wishlist_returns_not_found_on_public_read(): void
    {
        $response = $this->requestJson('GET', '/api/v2/shop/shared-wishlists/stale-share-token');

        $this->assertResponseCode($response, Response::HTTP_NOT_FOUND);
    }

    public function test_shared_wishlist_from_wrong_channel_returns_not_found(): void
    {
        $response = $this->requestJson('GET', '/api/v2/shop/shared-wishlists/other-channel-share-token-xyz');

        $this->assertResponseCode($response, Response::HTTP_NOT_FOUND);
    }

    public function test_guest_can_unshare_wishlist(): void
    {
        $response = $this->requestJson('PATCH', '/api/v2/shop/wishlists/shared-token/unshare');

        $this->assertResponse($response, 'Api/WishlistShareTest/unshare_wishlist_response', Response::HTTP_OK);
    }

    public function test_unshared_wishlist_not_readable_via_share_token_after_unshare(): void
    {
        $this->requestJson('PATCH', '/api/v2/shop/wishlists/shared-token/unshare');

        $response = $this->requestJson('GET', '/api/v2/shop/shared-wishlists/public-share-token-abc');

        $this->assertResponseCode($response, Response::HTTP_NOT_FOUND);
    }

    public function test_cannot_share_twice(): void
    {
        $this->requestJson('PATCH', '/api/v2/shop/wishlists/unshared-token/share');

        $response = $this->requestJson('PATCH', '/api/v2/shop/wishlists/unshared-token/share');

        $this->assertResponseCode($response, Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function test_cannot_unshare_wishlist_that_is_not_shared(): void
    {
        $response = $this->requestJson('PATCH', '/api/v2/shop/wishlists/unshared-token/unshare');

        $this->assertResponseCode($response, Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function test_wrong_token_cannot_share_another_wishlist(): void
    {
        $response = $this->requestJson('PATCH', '/api/v2/shop/wishlists/unknown-token/share');

        $this->assertResponseCode($response, Response::HTTP_NOT_FOUND);
    }
}
