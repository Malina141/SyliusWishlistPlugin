<?php

declare(strict_types=1);

namespace Tests\Malina141\SyliusWishlistPlugin\Functional\Api\Shop;

use Symfony\Component\BrowserKit\Cookie;
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

        self::assertArrayHasKey('token', $data);
        self::assertIsString($data['token']);
        self::assertNotSame('', $data['token']);
        self::assertSame(sprintf('/api/v2/shop/wishlists/%s', $data['token']), $data['@id']);

        $this->assertResponse($response, 'Api/WishlistTest/create_wishlist_response', Response::HTTP_CREATED);
    }

    public function test_guest_fetches_wishlist_by_token(): void
    {
        $this->client->request('GET', '/api/v2/shop/wishlists/api-wishlist-token', [], [], self::CONTENT_TYPE_HEADER);
        $response = $this->client->getResponse();

        $this->assertResponse($response, 'Api/WishlistTest/show_wishlist_response');
    }

    public function test_unknown_token_returns_not_found(): void
    {
        $this->client->request('GET', '/api/v2/shop/wishlists/unknown-wishlist-token');
        $response = $this->client->getResponse();

        self::assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    public function test_token_from_another_channel_returns_not_found(): void
    {
        $this->client->request('GET', '/api/v2/shop/wishlists/other-channel-wishlist-token');
        $response = $this->client->getResponse();

        self::assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }
}
