<?php

declare(strict_types=1);

namespace Tests\Malina141\SyliusWishlistPlugin\Functional\Api\Shop;

use Symfony\Component\HttpFoundation\Response;
use Tests\Malina141\SyliusWishlistPlugin\Functional\FunctionalTestCase;

final class WishlistTest extends FunctionalTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadFixturesFromFile('Api/WishlistTest/wishlist.yaml');
    }

    public function test_guest_creates_wishlist_and_receives_token(): void
    {
        $response = $this->requestJson('POST', '/api/v2/shop/wishlists');

        $data = json_decode($response->getContent(), true, 512, \JSON_THROW_ON_ERROR);

        $this->assertArrayHasKey('token', $data);
        $this->assertIsString($data['token']);
        $this->assertNotSame('', $data['token']);
        $this->assertSame(sprintf('/api/v2/shop/wishlists/%s', $data['token']), $data['@id']);

        $this->assertResponse($response, 'Api/WishlistTest/create_wishlist_response', Response::HTTP_CREATED);
    }

    public function test_guest_fetches_wishlist_by_token(): void
    {
        $response = $this->requestJson('GET', '/api/v2/shop/wishlists/api-wishlist-token');

        $this->assertResponse($response, 'Api/WishlistTest/show_wishlist_response');
    }

    public function test_guest_can_add_variant_to_wishlist(): void
    {
        $response = $this->requestJson('POST', '/api/v2/shop/wishlists/api-existing-token/items', [
            'productVariantCode' => 'API_PRODUCT_VARIANT',
        ]);

        $this->assertResponse($response, 'Api/WishlistTest/add_variant_to_wishlist_response', Response::HTTP_CREATED);
    }

    public function test_adding_the_same_variant_twice_does_not_create_duplicate(): void
    {
        $this->requestJson('POST', '/api/v2/shop/wishlists/api-existing-token/items', [
            'productVariantCode' => 'API_PRODUCT_VARIANT',
        ]);

        $response = $this->requestJson('POST', '/api/v2/shop/wishlists/api-existing-token/items', [
            'productVariantCode' => 'API_PRODUCT_VARIANT',
        ]);

        $this->assertResponse($response, 'Api/WishlistTest/add_variant_to_wishlist_response', Response::HTTP_CREATED);
    }

    public function test_guest_cannot_add_unknown_variant(): void
    {
        $response = $this->requestJson('POST', '/api/v2/shop/wishlists/api-existing-token/items', [
            'productVariantCode' => 'API_UNKNOWN_PRODUCT_VARIANT',
        ]);

        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
    }

    public function test_guest_cannot_add_disabled_variant(): void
    {
        $response = $this->requestJson('POST', '/api/v2/shop/wishlists/api-existing-token/items', [
            'productVariantCode' => 'API_DISABLED_PRODUCT_VARIANT',
        ]);

        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
    }

    public function test_guest_removes_variant_from_wishlist(): void
    {
        $this->client->request('DELETE', '/api/v2/shop/wishlists/api-existing-token/items/API_PRODUCT_VARIANT');
        $response = $this->client->getResponse();

        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());

        $response = $this->requestJson('GET', '/api/v2/shop/wishlists/api-existing-token');

        $this->assertResponse($response, 'Api/WishlistTest/show_existing_wishlist_after_item_removal_response');
    }

    public function test_guest_removes_missing_valid_variant_as_no_op(): void
    {
        $this->client->request('DELETE', '/api/v2/shop/wishlists/api-existing-token/items/API_OTHER_PRODUCT_VARIANT');
        $response = $this->client->getResponse();

        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
    }

    public function test_guest_cannot_remove_variant_from_unknown_wishlist(): void
    {
        $this->client->request('DELETE', '/api/v2/shop/wishlists/unknown-wishlist-token/items/API_PRODUCT_VARIANT');
        $response = $this->client->getResponse();

        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    public function test_guest_cannot_remove_variant_from_wishlist_in_another_channel(): void
    {
        $this->client->request('DELETE', '/api/v2/shop/wishlists/other-channel-wishlist-token/items/API_PRODUCT_VARIANT');
        $response = $this->client->getResponse();

        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    public function test_guest_cannot_remove_unknown_variant(): void
    {
        $this->client->request('DELETE', '/api/v2/shop/wishlists/api-existing-token/items/UNKNOWN_PRODUCT_VARIANT');
        $response = $this->client->getResponse();

        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
    }

    public function test_unknown_token_returns_not_found(): void
    {
        $this->client->request('GET', '/api/v2/shop/wishlists/unknown-wishlist-token');
        $response = $this->client->getResponse();

        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    public function test_token_from_another_channel_returns_not_found(): void
    {
        $this->client->request('GET', '/api/v2/shop/wishlists/other-channel-wishlist-token');
        $response = $this->client->getResponse();

        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }
}
