<?php

declare(strict_types=1);

namespace Tests\Malina141\SyliusWishlistPlugin\Unit\Api\Security;

use Malina141\SyliusWishlistPlugin\Api\Security\CartAccessChecker;
use Override;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Sylius\Bundle\ApiBundle\Context\UserContextInterface;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\ShopUserInterface;

final class CartAccessCheckerTest extends TestCase
{
    private UserContextInterface&Stub $userContext;

    private CartAccessChecker $cartAccessChecker;

    #[Override]
    protected function setUp(): void
    {
        $this->userContext = $this->createStub(UserContextInterface::class);
        $this->cartAccessChecker = new CartAccessChecker($this->userContext);
    }

    #[Test]
    public function cart_without_customer_is_accessible(): void
    {
        $cart = $this->createMock(OrderInterface::class);
        $cart->method('getCustomer')->willReturn(null);

        $this->assertTrue($this->cartAccessChecker->canAccess($cart));
    }

    #[Test]
    public function cart_with_customer_without_user_is_accessible(): void
    {
        $customer = $this->createMock(CustomerInterface::class);
        $customer->method('getUser')->willReturn(null);
        $cart = $this->createCartWithCustomer($customer);

        $this->assertTrue($this->cartAccessChecker->canAccess($cart));
    }

    #[Test]
    public function customer_cart_is_accessible_to_current_customer_user(): void
    {
        $customer = $this->createCustomer(10);
        $cartUser = $this->createMock(ShopUserInterface::class);
        $customer->method('getUser')->willReturn($cartUser);
        $currentUser = $this->createMock(ShopUserInterface::class);
        $currentUser->method('getCustomer')->willReturn($customer);
        $this->userContext->method('getUser')->willReturn($currentUser);

        $this->assertTrue($this->cartAccessChecker->canAccess($this->createCartWithCustomer($customer)));
    }

    #[Test]
    public function customer_cart_is_not_accessible_anonymously(): void
    {
        $customer = $this->createCustomer(10);
        $customer->method('getUser')->willReturn($this->createMock(ShopUserInterface::class));
        $this->userContext->method('getUser')->willReturn(null);

        $this->assertFalse($this->cartAccessChecker->canAccess($this->createCartWithCustomer($customer)));
    }

    #[Test]
    public function customer_cart_is_not_accessible_to_another_customer_user(): void
    {
        $cartCustomer = $this->createCustomer(10);
        $currentCustomer = $this->createCustomer(20);
        $cartCustomer->method('getUser')->willReturn($this->createMock(ShopUserInterface::class));
        $currentUser = $this->createMock(ShopUserInterface::class);
        $currentUser->method('getCustomer')->willReturn($currentCustomer);
        $this->userContext->method('getUser')->willReturn($currentUser);

        $this->assertFalse($this->cartAccessChecker->canAccess($this->createCartWithCustomer($cartCustomer)));
    }

    #[Test]
    public function customer_cart_is_not_accessible_when_current_user_has_no_customer(): void
    {
        $cartCustomer = $this->createCustomer(10);
        $cartCustomer->method('getUser')->willReturn($this->createMock(ShopUserInterface::class));
        $currentUser = $this->createMock(ShopUserInterface::class);
        $currentUser->method('getCustomer')->willReturn(null);
        $this->userContext->method('getUser')->willReturn($currentUser);

        $this->assertFalse($this->cartAccessChecker->canAccess($this->createCartWithCustomer($cartCustomer)));
    }

    private function createCartWithCustomer(CustomerInterface $customer): OrderInterface
    {
        $cart = $this->createMock(OrderInterface::class);
        $cart->method('getCustomer')->willReturn($customer);

        return $cart;
    }

    private function createCustomer(int $id): CustomerInterface&MockObject
    {
        $customer = $this->createMock(CustomerInterface::class);
        $customer->method('getId')->willReturn($id);

        return $customer;
    }
}
