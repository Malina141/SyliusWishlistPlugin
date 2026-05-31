<?php

declare(strict_types=1);

namespace Tests\Malina141\SyliusWishlistPlugin\Unit\Api\Security;

use Malina141\SyliusWishlistPlugin\Api\Security\WishlistAccessChecker;
use Malina141\SyliusWishlistPlugin\Entity\WishlistInterface;
use Override;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Sylius\Bundle\ApiBundle\Context\UserContextInterface;
use Sylius\Component\Core\Model\ShopUserInterface;

final class WishlistAccessCheckerTest extends TestCase
{
    private UserContextInterface&Stub $userContext;

    private WishlistAccessChecker $accessChecker;

    #[Override]
    protected function setUp(): void
    {
        $this->userContext = $this->createStub(UserContextInterface::class);
        $this->accessChecker = new WishlistAccessChecker($this->userContext);
    }

    #[Test]
    public function unowned_wishlist_is_accessible_anonymously(): void
    {
        $wishlist = $this->createWishlistWithoutOwner();
        $this->userContext->method('getUser')->willReturn(null);

        $this->assertTrue($this->accessChecker->canAccessPrivateToken($wishlist));
    }

    #[Test]
    public function unowned_wishlist_is_accessible_to_logged_in_user(): void
    {
        $wishlist = $this->createWishlistWithoutOwner();
        $user = $this->createShopUser(1);
        $this->userContext->method('getUser')->willReturn($user);

        $this->assertTrue($this->accessChecker->canAccessPrivateToken($wishlist));
    }

    #[Test]
    public function owned_wishlist_is_accessible_to_owner(): void
    {
        $owner = $this->createShopUser(1);
        $wishlist = $this->createWishlistWithOwner($owner);
        $this->userContext->method('getUser')->willReturn($owner);

        $this->assertTrue($this->accessChecker->canAccessPrivateToken($wishlist));
    }

    #[Test]
    public function owned_wishlist_is_inaccessible_to_another_logged_in_user(): void
    {
        $owner = $this->createShopUser(1);
        $otherUser = $this->createShopUser(2);
        $wishlist = $this->createWishlistWithOwner($owner);
        $this->userContext->method('getUser')->willReturn($otherUser);

        $this->assertFalse($this->accessChecker->canAccessPrivateToken($wishlist));
    }

    #[Test]
    public function owned_wishlist_is_inaccessible_anonymously(): void
    {
        $owner = $this->createShopUser(1);
        $wishlist = $this->createWishlistWithOwner($owner);
        $this->userContext->method('getUser')->willReturn(null);

        $this->assertFalse($this->accessChecker->canAccessPrivateToken($wishlist));
    }

    private function createWishlistWithoutOwner(): WishlistInterface
    {
        $wishlist = $this->createMock(WishlistInterface::class);
        $wishlist->method('getOwner')->willReturn(null);

        return $wishlist;
    }

    private function createWishlistWithOwner(ShopUserInterface $owner): WishlistInterface
    {
        $wishlist = $this->createMock(WishlistInterface::class);
        $wishlist->method('getOwner')->willReturn($owner);

        return $wishlist;
    }

    private function createShopUser(?int $id): ShopUserInterface&MockObject
    {
        $user = $this->createMock(ShopUserInterface::class);
        $user->method('getId')->willReturn($id);

        return $user;
    }
}
