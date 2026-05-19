<?php

declare(strict_types=1);

namespace Tests\Malina141\SyliusWishlistPlugin\Unit\Adder;

use Doctrine\ORM\EntityManagerInterface;
use Malina141\SyliusWishlistPlugin\Adder\WishlistItemsToCartAdder;
use Malina141\SyliusWishlistPlugin\Entity\WishlistItemInterface;
use Malina141\SyliusWishlistPlugin\Validator\AddToCartCommandValidatorInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Sylius\Bundle\OrderBundle\Controller\AddToCartCommandInterface;
use Sylius\Bundle\OrderBundle\Factory\AddToCartCommandFactoryInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\OrderItemInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Order\Modifier\OrderItemQuantityModifierInterface;
use Sylius\Component\Order\Modifier\OrderModifierInterface;
use Sylius\Resource\Factory\FactoryInterface;

#[CoversClass(WishlistItemsToCartAdder::class)]
final class WishlistItemsToCartAdderTest extends TestCase
{
    private FactoryInterface&Stub $orderItemFactory;

    private AddToCartCommandFactoryInterface&MockObject $addToCartCommandFactory;

    private AddToCartCommandValidatorInterface&Stub $addToCartCommandValidator;

    private OrderItemQuantityModifierInterface&MockObject $orderItemQuantityModifier;

    private OrderModifierInterface&MockObject $orderModifier;

    private EntityManagerInterface&MockObject $entityManager;

    private WishlistItemsToCartAdder $wishlistItemsToCartAdder;

    protected function setUp(): void
    {
        $this->orderItemFactory = $this->createStub(FactoryInterface::class);
        $this->addToCartCommandFactory = $this->createStub(AddToCartCommandFactoryInterface::class);
        $this->addToCartCommandValidator = $this->createStub(AddToCartCommandValidatorInterface::class);
        $this->orderItemQuantityModifier = $this->createMock(OrderItemQuantityModifierInterface::class);
        $this->orderModifier = $this->createMock(OrderModifierInterface::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);

        $this->wishlistItemsToCartAdder = new WishlistItemsToCartAdder(
            $this->orderItemFactory,
            $this->orderItemQuantityModifier,
            $this->orderModifier,
            $this->entityManager,
            $this->addToCartCommandFactory,
            $this->addToCartCommandValidator
        );
    }

    #[Test]
    public function adding_an_empty_wishlist_leaves_the_cart_unmodified(): void
    {
        $cart = $this->createMock(OrderInterface::class);

        $this->orderModifier->expects($this->never())->method('addToOrder');
        $this->entityManager->expects($this->once())->method('persist')->with($cart);
        $this->entityManager->expects($this->once())->method('flush');

        $this->wishlistItemsToCartAdder->add([], $cart);
    }

    #[Test]
    public function adding_an_item_without_a_product_variant_is_skipped(): void
    {
        $wishlistItem = $this->createStub(WishlistItemInterface::class);
        $wishlistItem->method('getProductVariant')->willReturn(null);
        $cart = $this->createMock(OrderInterface::class);

        $this->orderModifier->expects($this->never())->method('addToOrder');
        $this->entityManager->expects($this->once())->method('persist')->with($cart);
        $this->entityManager->expects($this->once())->method('flush');

        $this->wishlistItemsToCartAdder->add([$wishlistItem], $cart);
    }

    #[Test]
    public function adding_an_invalid_item_is_skipped(): void
    {
        $cart = $this->createMock(OrderInterface::class);
        $productVariant = $this->createStub(ProductVariantInterface::class);
        $wishlistItem = $this->createStub(WishlistItemInterface::class);
        $wishlistItem->method('getProductVariant')->willReturn($productVariant);
        $orderItem = $this->createMock(OrderItemInterface::class);

        $this->orderItemFactory->method('createNew')->willReturn($orderItem);
        $orderItem->expects($this->once())->method('setVariant')->with($productVariant);
        $this->addToCartCommandFactory->method('createWithCartAndCartItem')->with($cart, $orderItem)->willReturn($this->createStub(AddToCartCommandInterface::class));
        $this->addToCartCommandValidator->method('isValid')->willReturn(false);
        $this->orderItemQuantityModifier->expects($this->once())->method('modify')->with($orderItem, 1);
        $this->orderModifier->expects($this->never())->method('addToOrder');
        $this->entityManager->expects($this->once())->method('persist')->with($cart);
        $this->entityManager->expects($this->once())->method('flush');

        $this->wishlistItemsToCartAdder->add([$wishlistItem], $cart);
    }

    #[Test]
    public function adding_valid_items_adds_them_to_the_cart(): void
    {
        $cart = $this->createMock(OrderInterface::class);
        $productVariant = $this->createStub(ProductVariantInterface::class);
        $wishlistItem = $this->createStub(WishlistItemInterface::class);
        $wishlistItem->method('getProductVariant')->willReturn($productVariant);
        $orderItem = $this->createMock(OrderItemInterface::class);

        $this->orderItemFactory->method('createNew')->willReturn($orderItem);
        $orderItem->expects($this->once())->method('setVariant')->with($productVariant);

        $command = $this->createStub(AddToCartCommandInterface::class);
        $this->addToCartCommandFactory->method('createWithCartAndCartItem')->with($cart, $orderItem)->willReturn($command);
        $this->addToCartCommandValidator->method('isValid')->willReturn(true);
        $this->orderItemQuantityModifier->expects($this->once())->method('modify')->with($orderItem, 1);
        $this->orderModifier->expects($this->once())->method('addToOrder')->with($cart, $orderItem);

        $this->entityManager->expects($this->once())->method('persist')->with($cart);
        $this->entityManager->expects($this->once())->method('flush');

        $this->wishlistItemsToCartAdder->add([$wishlistItem], $cart);
    }

    #[Test]
    public function adding_both_valid_and_invalid_items_only_adds_the_valid_ones_to_the_cart(): void
    {
        $cart = $this->createMock(OrderInterface::class);
        $productVariant = $this->createStub(ProductVariantInterface::class);

        $validWishlistItem = $this->createStub(WishlistItemInterface::class);
        $validWishlistItem->method('getProductVariant')->willReturn($productVariant);

        $invalidWishlistItem = $this->createStub(WishlistItemInterface::class);
        $invalidWishlistItem->method('getProductVariant')->willReturn($productVariant);

        $validOrderItem = $this->createMock(OrderItemInterface::class);
        $invalidOrderItem = $this->createMock(OrderItemInterface::class);

        $this->orderItemFactory->method('createNew')->willReturnOnConsecutiveCalls($validOrderItem, $invalidOrderItem);
        $validOrderItem->expects($this->once())->method('setVariant')->with($productVariant);
        $invalidOrderItem->expects($this->once())->method('setVariant')->with($productVariant);

        $validCommand = $this->createStub(AddToCartCommandInterface::class);
        $invalidCommand = $this->createStub(AddToCartCommandInterface::class);

        $this->addToCartCommandFactory->method('createWithCartAndCartItem')->willReturnOnConsecutiveCalls($validCommand, $invalidCommand);

        $this->addToCartCommandValidator->method('isValid')->willReturnCallback(fn (AddToCartCommandInterface $command): bool => $command === $validCommand);

        $this->orderItemQuantityModifier->expects($this->exactly(2))->method('modify');

        $this->orderModifier->expects($this->once())->method('addToOrder')->with($cart, $validOrderItem);

        $this->entityManager->expects($this->once())->method('persist')->with($cart);
        $this->entityManager->expects($this->once())->method('flush');

        $this->wishlistItemsToCartAdder->add([$validWishlistItem, $invalidWishlistItem], $cart);
    }

    #[Test]
    public function adding_multiple_valid_items_adds_all_of_them_to_the_cart(): void
    {
        $cart = $this->createMock(OrderInterface::class);

        $firstVariant = $this->createStub(ProductVariantInterface::class);
        $firstWishlistItem = $this->createStub(WishlistItemInterface::class);
        $firstWishlistItem->method('getProductVariant')->willReturn($firstVariant);

        $secondVariant = $this->createStub(ProductVariantInterface::class);
        $secondWishlistItem = $this->createStub(WishlistItemInterface::class);
        $secondWishlistItem->method('getProductVariant')->willReturn($secondVariant);

        $firstOrderItem = $this->createMock(OrderItemInterface::class);
        $secondOrderItem = $this->createMock(OrderItemInterface::class);

        $this->orderItemFactory->method('createNew')->willReturnOnConsecutiveCalls($firstOrderItem, $secondOrderItem);
        $firstOrderItem->expects($this->once())->method('setVariant')->with($firstVariant);
        $secondOrderItem->expects($this->once())->method('setVariant')->with($secondVariant);

        $this->addToCartCommandFactory->method('createWithCartAndCartItem')->willReturn($this->createStub(AddToCartCommandInterface::class));
        $this->addToCartCommandValidator->method('isValid')->willReturn(true);

        $this->orderItemQuantityModifier->expects($this->exactly(2))->method('modify');

        $addToOrderInvokeCount = $this->exactly(2);
        $this->orderModifier->expects($addToOrderInvokeCount)->method('addToOrder')
            ->willReturnCallback(function (OrderInterface $order, OrderItemInterface $item) use ($cart, $firstOrderItem, $secondOrderItem, $addToOrderInvokeCount): void {
                $this->assertSame($cart, $order);

                match ($addToOrderInvokeCount->numberOfInvocations()) {
                    1 => $this->assertSame($firstOrderItem, $item),
                    2 => $this->assertSame($secondOrderItem, $item),
                };
            });

        $this->entityManager->expects($this->once())->method('persist')->with($cart);
        $this->entityManager->expects($this->once())->method('flush');

        $this->wishlistItemsToCartAdder->add([$firstWishlistItem, $secondWishlistItem], $cart);
    }

    #[Test]
    public function adding_a_null_variant_item_alongside_a_valid_item_only_adds_the_valid_one(): void
    {
        $cart = $this->createMock(OrderInterface::class);

        $nullVariantItem = $this->createStub(WishlistItemInterface::class);
        $nullVariantItem->method('getProductVariant')->willReturn(null);

        $productVariant = $this->createStub(ProductVariantInterface::class);
        $validWishlistItem = $this->createStub(WishlistItemInterface::class);
        $validWishlistItem->method('getProductVariant')->willReturn($productVariant);

        $orderItem = $this->createMock(OrderItemInterface::class);
        $this->orderItemFactory->method('createNew')->willReturn($orderItem);
        $orderItem->expects($this->once())->method('setVariant')->with($productVariant);

        $command = $this->createStub(AddToCartCommandInterface::class);
        $this->addToCartCommandFactory->method('createWithCartAndCartItem')->willReturn($command);
        $this->addToCartCommandValidator->method('isValid')->willReturn(true);

        $this->orderItemQuantityModifier->expects($this->once())->method('modify')->with($orderItem, 1);
        $this->orderModifier->expects($this->once())->method('addToOrder')->with($cart, $orderItem);

        $this->entityManager->expects($this->once())->method('persist')->with($cart);
        $this->entityManager->expects($this->once())->method('flush');

        $this->wishlistItemsToCartAdder->add([$nullVariantItem, $validWishlistItem], $cart);
    }
}
