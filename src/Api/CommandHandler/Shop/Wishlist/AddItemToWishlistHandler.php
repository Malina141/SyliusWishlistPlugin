<?php

declare(strict_types=1);

namespace Malina141\SyliusWishlistPlugin\Api\CommandHandler\Shop\Wishlist;

use Malina141\SyliusWishlistPlugin\Api\Command\Shop\Wishlist\AddItemToWishlist;
use Malina141\SyliusWishlistPlugin\Api\Security\WishlistAccessCheckerInterface;
use Malina141\SyliusWishlistPlugin\Entity\WishlistInterface;
use Malina141\SyliusWishlistPlugin\Modifier\WishlistModifierInterface;
use Malina141\SyliusWishlistPlugin\Repository\WishlistRepositoryInterface;
use Sylius\Component\Channel\Repository\ChannelRepositoryInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Core\Repository\ProductVariantRepositoryInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Webmozart\Assert\Assert;

final readonly class AddItemToWishlistHandler
{
    public function __construct(
        private WishlistRepositoryInterface $wishlistRepository,
        private ProductVariantRepositoryInterface $productVariantRepository,
        private ChannelRepositoryInterface $channelRepository,
        private WishlistModifierInterface $wishlistModifier,
        private WishlistAccessCheckerInterface $accessChecker,
    ) {
    }

    public function __invoke(AddItemToWishlist $command): WishlistInterface
    {
        $channel = $this->channelRepository->findOneByCode($command->channelCode);
        Assert::isInstanceOf($channel, ChannelInterface::class);

        $wishlist = $this->wishlistRepository->findOneByTokenAndChannel($command->wishlistToken, $channel);
        if (!$wishlist instanceof WishlistInterface) {
            throw new NotFoundHttpException('Wishlist not found');
        }

        if (!$this->accessChecker->canAccessPrivateToken($wishlist)) {
            throw new NotFoundHttpException('Wishlist not found');
        }

        $productVariant = $this->productVariantRepository->findOneBy(['code' => $command->productVariantCode, 'enabled' => true]);
        if (!$productVariant instanceof ProductVariantInterface) {
            throw new UnprocessableEntityHttpException(\sprintf('Product variant with code "%s" was not found ', $command->productVariantCode));
        }

        $product = $productVariant->getProduct();
        Assert::isInstanceOf($product, ProductInterface::class);

        if (!$product->isEnabled() || !$product->hasChannel($channel)) {
            throw new NotFoundHttpException('Product not found');
        }

        $this->wishlistModifier->addVariant($wishlist, $productVariant);

        return $wishlist;
    }
}
