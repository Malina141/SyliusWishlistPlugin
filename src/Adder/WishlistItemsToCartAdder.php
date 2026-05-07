<?php

declare(strict_types=1);

namespace Malina141\SyliusWishlistPlugin\Adder;

use Doctrine\ORM\EntityManagerInterface;
use Malina141\SyliusWishlistPlugin\Entity\WishlistItem;
use Malina141\SyliusWishlistPlugin\Validator\AddToCartCommandValidatorInterface;
use Sylius\Bundle\OrderBundle\Factory\AddToCartCommandFactoryInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\OrderItemInterface;
use Sylius\Component\Order\Modifier\OrderItemQuantityModifierInterface;
use Sylius\Component\Order\Modifier\OrderModifierInterface;
use Sylius\Resource\Factory\FactoryInterface;

final readonly class WishlistItemsToCartAdder implements WishlistItemsToCartAdderInterface
{
    /**
     * @param FactoryInterface<OrderItemInterface> $orderItemFactory
     */
    public function __construct(
        private FactoryInterface $orderItemFactory,
        private OrderItemQuantityModifierInterface $orderItemQuantityModifier,
        private OrderModifierInterface $orderModifier,
        private EntityManagerInterface $orderManager,
        private AddToCartCommandFactoryInterface $addToCartCommandFactory,
        private AddToCartCommandValidatorInterface $addToCartCommandValidator,
    ) {
    }

    public function add(array $wishlistItems, OrderInterface $cart): void
    {
        /** @var WishlistItem $wishlistItem */
        foreach ($wishlistItems as $wishlistItem) {
            $productVariant = $wishlistItem->getProductVariant();
            if (null === $productVariant) {
                continue;
            }

            /** @var OrderItemInterface $orderItem */
            $orderItem = $this->orderItemFactory->createNew();
            $orderItem->setVariant($productVariant);
            $this->orderItemQuantityModifier->modify($orderItem, 1);

            $command = $this->addToCartCommandFactory->createWithCartAndCartItem($cart, $orderItem);
            if (false === $this->addToCartCommandValidator->isValid($command)) {
                continue;
            }

            $this->orderModifier->addToOrder($cart, $orderItem);
        }

        $this->orderManager->persist($cart);
        $this->orderManager->flush();
    }
}
