<?php

declare(strict_types=1);

namespace Tests\Malina141\SyliusWishlistPlugin\Functional\Api\Shop;

use Malina141\SyliusWishlistPlugin\Entity\WishlistItemInterface;
use Symfony\Component\HttpFoundation\Response;
use Tests\Malina141\SyliusWishlistPlugin\Functional\FunctionalTestCase;

final class WishlistOwnershipTest extends FunctionalTestCase
{
    private array $fixtures;

    protected function setUp(): void
    {
        parent::setUp();

        $this->fixtures = $this->loadFixturesFromFile('Api/WishlistOwnershipTest/wishlist.yaml');
    }

    public function test_anonymous_user_can_read_unowned_wishlist_by_private_token(): void
    {
        $response = $this->requestJson('GET', '/api/v2/shop/wishlists/guest-wishlist-token');

        $this->assertResponseCode($response, Response::HTTP_OK);
    }

    public function test_anonymous_user_cannot_read_customer_owned_wishlist_by_private_token(): void
    {
        $response = $this->requestJson('GET', '/api/v2/shop/wishlists/customer-a-wishlist-token');

        $this->assertResponseCode($response, Response::HTTP_NOT_FOUND);
    }

    public function test_customer_a_can_read_their_own_wishlist_by_private_token(): void
    {
        $token = $this->getJwtToken('customer-a@example.com', 'testPassword');

        $response = $this->requestAuthenticatedJson('GET', '/api/v2/shop/wishlists/customer-a-wishlist-token', token: $token);

        $this->assertResponseCode($response, Response::HTTP_OK);
    }

    public function test_customer_a_cannot_read_customer_b_wishlist_by_private_token(): void
    {
        $token = $this->getJwtToken('customer-a@example.com', 'testPassword');

        $response = $this->requestAuthenticatedJson('GET', '/api/v2/shop/wishlists/customer-b-wishlist-token', token: $token);

        $this->assertResponseCode($response, Response::HTTP_NOT_FOUND);
    }

    public function test_customer_a_can_add_item_to_own_wishlist(): void
    {
        $token = $this->getJwtToken('customer-a@example.com', 'testPassword');

        $response = $this->requestAuthenticatedJson(
            'POST',
            '/api/v2/shop/wishlists/customer-a-wishlist-token/items',
            ['productVariantCode' => 'API_OTHER_PRODUCT_VARIANT'],
            $token,
        );

        $this->assertResponseCode($response, Response::HTTP_CREATED);
    }

    public function test_customer_a_cannot_add_item_to_customer_b_wishlist(): void
    {
        $token = $this->getJwtToken('customer-a@example.com', 'testPassword');

        $response = $this->requestAuthenticatedJson(
            'POST',
            '/api/v2/shop/wishlists/customer-b-wishlist-token/items',
            ['productVariantCode' => 'API_OTHER_PRODUCT_VARIANT'],
            $token,
        );

        $this->assertResponseCode($response, Response::HTTP_NOT_FOUND);
    }

    public function test_customer_a_cannot_add_unknown_item_to_customer_b_wishlist_without_leaking_variant_validation(): void
    {
        $token = $this->getJwtToken('customer-a@example.com', 'testPassword');

        $response = $this->requestAuthenticatedJson(
            'POST',
            '/api/v2/shop/wishlists/customer-b-wishlist-token/items',
            ['productVariantCode' => 'UNKNOWN_PRODUCT_VARIANT'],
            $token,
        );

        $this->assertResponseCode($response, Response::HTTP_NOT_FOUND);
    }

    public function test_customer_a_can_remove_item_from_own_wishlist(): void
    {
        $token = $this->getJwtToken('customer-a@example.com', 'testPassword');

        $this->client->request(
            'DELETE',
            '/api/v2/shop/wishlists/customer-a-wishlist-token/items/API_PRODUCT_VARIANT',
            [],
            [],
            [
                'HTTP_ACCEPT' => 'application/ld+json',
                'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
            ],
        );

        $response = $this->client->getResponse();

        $this->assertResponseCode($response, Response::HTTP_NO_CONTENT);
    }

    public function test_customer_a_cannot_remove_item_from_customer_b_wishlist(): void
    {
        $token = $this->getJwtToken('customer-a@example.com', 'testPassword');

        $this->client->request(
            'DELETE',
            '/api/v2/shop/wishlists/customer-b-wishlist-token/items/API_PRODUCT_VARIANT',
            [],
            [],
            [
                'HTTP_ACCEPT' => 'application/ld+json',
                'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
            ],
        );

        $response = $this->client->getResponse();

        $this->assertResponseCode($response, Response::HTTP_NOT_FOUND);
    }

    public function test_customer_a_can_rename_own_wishlist(): void
    {
        $token = $this->getJwtToken('customer-a@example.com', 'testPassword');

        $response = $this->requestAuthenticatedJson(
            'PATCH',
            '/api/v2/shop/wishlists/customer-a-wishlist-token',
            ['name' => 'New Name'],
            $token,
        );

        $this->assertResponseCode($response, Response::HTTP_OK);
    }

    public function test_customer_a_cannot_rename_customer_b_wishlist(): void
    {
        $token = $this->getJwtToken('customer-a@example.com', 'testPassword');

        $response = $this->requestAuthenticatedJson(
            'PATCH',
            '/api/v2/shop/wishlists/customer-b-wishlist-token',
            ['name' => 'New Name'],
            $token,
        );

        $this->assertResponseCode($response, Response::HTTP_NOT_FOUND);
    }

    public function test_customer_a_can_share_own_wishlist(): void
    {
        $token = $this->getJwtToken('customer-a@example.com', 'testPassword');

        $response = $this->requestAuthenticatedJson(
            'PATCH',
            '/api/v2/shop/wishlists/customer-a-wishlist-token/share',
            token: $token,
        );

        $this->assertResponseCode($response, Response::HTTP_OK);
    }

    public function test_customer_a_cannot_share_customer_b_wishlist(): void
    {
        $token = $this->getJwtToken('customer-a@example.com', 'testPassword');

        $response = $this->requestAuthenticatedJson(
            'PATCH',
            '/api/v2/shop/wishlists/customer-b-wishlist-token/share',
            token: $token,
        );

        $this->assertResponseCode($response, Response::HTTP_NOT_FOUND);
    }

    public function test_customer_a_can_unshare_own_wishlist(): void
    {
        $token = $this->getJwtToken('customer-a@example.com', 'testPassword');

        $this->requestAuthenticatedJson(
            'PATCH',
            '/api/v2/shop/wishlists/customer-a-wishlist-token/share',
            token: $token,
        );

        $response = $this->requestAuthenticatedJson(
            'PATCH',
            '/api/v2/shop/wishlists/customer-a-wishlist-token/unshare',
            token: $token,
        );

        $this->assertResponseCode($response, Response::HTTP_OK);
    }

    public function test_customer_a_cannot_unshare_customer_b_wishlist(): void
    {
        $token = $this->getJwtToken('customer-a@example.com', 'testPassword');

        $response = $this->requestAuthenticatedJson(
            'PATCH',
            '/api/v2/shop/wishlists/customer-b-wishlist-token/unshare',
            token: $token,
        );

        $this->assertResponseCode($response, Response::HTTP_NOT_FOUND);
    }

    public function test_customer_a_can_add_own_wishlist_items_to_own_cart(): void
    {
        $token = $this->getJwtToken('customer-a@example.com', 'testPassword');

        $response = $this->requestAuthenticatedJson(
            'POST',
            '/api/v2/shop/wishlists/customer-a-wishlist-token/add-to-cart',
            [
                'wishlistItemIds' => [$this->getWishlistItemId('customer_a_wishlist_item')],
                'orderTokenValue' => 'customer-a-cart-token',
            ],
            $token,
        );

        $this->assertResponseCode($response, Response::HTTP_CREATED);
    }

    public function test_customer_a_cannot_add_own_wishlist_items_to_customer_b_cart(): void
    {
        $token = $this->getJwtToken('customer-a@example.com', 'testPassword');

        $response = $this->requestAuthenticatedJson(
            'POST',
            '/api/v2/shop/wishlists/customer-a-wishlist-token/add-to-cart',
            [
                'wishlistItemIds' => [$this->getWishlistItemId('customer_a_wishlist_item')],
                'orderTokenValue' => 'customer-b-cart-token',
            ],
            $token,
        );

        $this->assertResponseCode($response, Response::HTTP_NOT_FOUND);
    }

    public function test_customer_a_cannot_add_customer_b_wishlist_items_to_own_cart(): void
    {
        $token = $this->getJwtToken('customer-a@example.com', 'testPassword');

        $response = $this->requestAuthenticatedJson(
            'POST',
            '/api/v2/shop/wishlists/customer-b-wishlist-token/add-to-cart',
            [
                'wishlistItemIds' => [$this->getWishlistItemId('customer_b_wishlist_item')],
                'orderTokenValue' => 'customer-a-cart-token',
            ],
            $token,
        );

        $this->assertResponseCode($response, Response::HTTP_NOT_FOUND);
    }

    public function test_owner_can_access_their_own_wishlist_and_others_cannot(): void
    {
        $ownerToken = $this->getJwtToken('customer-a@example.com', 'testPassword');
        $otherToken = $this->getJwtToken('customer-b@example.com', 'testPassword');

        $getResponse = $this->requestAuthenticatedJson('GET', '/api/v2/shop/wishlists/customer-a-wishlist-token', token: $ownerToken);

        $this->assertResponseCode($getResponse, Response::HTTP_OK);

        $deniedResponse = $this->requestAuthenticatedJson('GET', '/api/v2/shop/wishlists/customer-a-wishlist-token', token: $otherToken);

        $this->assertResponseCode($deniedResponse, Response::HTTP_NOT_FOUND);
    }

    public function test_authenticated_create_returns_conflict_when_wishlist_already_exists_for_channel(): void
    {
        $token = $this->getJwtToken('customer-a@example.com', 'testPassword');

        $response = $this->requestAuthenticatedJson('POST', '/api/v2/shop/wishlists', [], $token);

        $this->assertResponseCode($response, Response::HTTP_CONFLICT);
    }

    public function test_authenticated_create_wishlist_creates_owned_wishlist(): void
    {
        $token = $this->getJwtToken('customer-c@example.com', 'testPassword');

        $response = $this->requestAuthenticatedJson('POST', '/api/v2/shop/wishlists', [], $token);
        $data = json_decode($response->getContent(), true, 512, \JSON_THROW_ON_ERROR);

        $this->assertResponseCode($response, Response::HTTP_CREATED);
        $this->assertArrayHasKey('token', $data);
        $this->assertIsString($data['token']);

        $getResponse = $this->requestAuthenticatedJson('GET', '/api/v2/shop/wishlists/' . $data['token'], token: $token);
        $anonymousGetResponse = $this->requestJson('GET', '/api/v2/shop/wishlists/' . $data['token']);

        $this->assertResponseCode($getResponse, Response::HTTP_OK);
        $this->assertResponseCode($anonymousGetResponse, Response::HTTP_NOT_FOUND);
    }

    public function test_anonymous_create_wishlist_creates_unowned_wishlist(): void
    {
        $response = $this->requestJson('POST', '/api/v2/shop/wishlists');

        $data = json_decode($response->getContent(), true, 512, \JSON_THROW_ON_ERROR);

        $this->assertResponseCode($response, Response::HTTP_CREATED);
        $this->assertArrayHasKey('token', $data);
        $this->assertIsString($data['token']);

        $wishlistToken = $data['token'];

        $getResponse = $this->requestJson('GET', '/api/v2/shop/wishlists/' . $wishlistToken);

        $this->assertResponseCode($getResponse, Response::HTTP_OK);
    }

    public function test_existing_guest_token_flow_still_works_for_mutations_and_add_to_cart(): void
    {
        $createResponse = $this->requestJson('POST', '/api/v2/shop/wishlists');
        $createData = json_decode($createResponse->getContent(), true, 512, \JSON_THROW_ON_ERROR);
        $wishlistToken = $createData['token'];

        $getResponse = $this->requestJson('GET', '/api/v2/shop/wishlists/' . $wishlistToken);
        $this->assertResponseCode($getResponse, Response::HTTP_OK);

        $addResponse = $this->requestJson(
            'POST',
            '/api/v2/shop/wishlists/' . $wishlistToken . '/items',
            ['productVariantCode' => 'API_PRODUCT_VARIANT'],
        );
        $this->assertResponseCode($addResponse, Response::HTTP_CREATED);
        $addData = json_decode($addResponse->getContent(), true, 512, \JSON_THROW_ON_ERROR);
        $this->assertArrayHasKey('items', $addData);
        $this->assertNotEmpty($addData['items']);

        $this->client->request(
            'DELETE',
            '/api/v2/shop/wishlists/' . $wishlistToken . '/items/API_PRODUCT_VARIANT',
            [],
            [],
            self::CONTENT_TYPE_HEADER,
        );
        $removeResponse = $this->client->getResponse();
        $this->assertResponseCode($removeResponse, Response::HTTP_NO_CONTENT);

        $patchResponse = $this->requestJson('PATCH', '/api/v2/shop/wishlists/' . $wishlistToken, ['name' => 'My List']);
        $this->assertResponseCode($patchResponse, Response::HTTP_OK);

        $shareResponse = $this->requestJson('PATCH', '/api/v2/shop/wishlists/' . $wishlistToken . '/share');
        $this->assertResponseCode($shareResponse, Response::HTTP_OK);

        $unshareResponse = $this->requestJson('PATCH', '/api/v2/shop/wishlists/' . $wishlistToken . '/unshare');
        $this->assertResponseCode($unshareResponse, Response::HTTP_OK);

        $reAddResponse = $this->requestJson(
            'POST',
            '/api/v2/shop/wishlists/' . $wishlistToken . '/items',
            ['productVariantCode' => 'API_PRODUCT_VARIANT'],
        );
        $this->assertResponseCode($reAddResponse, Response::HTTP_CREATED);
        $reAddData = json_decode($reAddResponse->getContent(), true, 512, \JSON_THROW_ON_ERROR);
        $this->assertArrayHasKey('items', $reAddData);
        $this->assertNotEmpty($reAddData['items']);
        $wishlistItemId = $reAddData['items'][0]['id'];
        $this->assertIsInt($wishlistItemId);

        $addToCartResponse = $this->requestJson(
            'POST',
            '/api/v2/shop/wishlists/' . $wishlistToken . '/add-to-cart',
            [
                'wishlistItemIds' => [$wishlistItemId],
                'orderTokenValue' => 'guest-cart-token',
            ],
        );
        $this->assertResponseCode($addToCartResponse, Response::HTTP_CREATED);
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

    private function requestAuthenticatedJson(string $method, string $uri, array $content = [], string $token = ''): Response
    {
        $headers = [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/ld+json',
        ];

        if ('' !== $token) {
            $headers['HTTP_AUTHORIZATION'] = 'Bearer ' . $token;
        }

        if ('PATCH' === $method) {
            $headers['CONTENT_TYPE'] = 'application/merge-patch+json';
        }

        $this->client->request($method, $uri, [], [], $headers, \json_encode($content));

        return $this->client->getResponse();
    }

    private function getJwtToken(string $email, string $password): string
    {
        $this->client->request(
            'POST',
            '/api/v2/shop/customers/token',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_ACCEPT' => 'application/ld+json',
            ],
            \json_encode(['email' => $email, 'password' => $password]),
        );

        $response = $this->client->getResponse();
        $content = json_decode($response->getContent(), true, 512, \JSON_THROW_ON_ERROR);

        if (!isset($content['token'])) {
            throw new \RuntimeException(sprintf('Could not get JWT token for user "%s". Response: %s', $email, $response->getContent()));
        }

        return $content['token'];
    }
}
