<?php

declare(strict_types=1);

namespace Tests\Malina141\SyliusWishlistPlugin\Unit\Api\CommandHandler\Shop\Wishlist;

use Malina141\SyliusWishlistPlugin\Api\Command\Shop\Wishlist\AddItemToWishlist;
use Malina141\SyliusWishlistPlugin\Api\CommandHandler\Shop\Wishlist\AddItemToWishlistHandler;
use Malina141\SyliusWishlistPlugin\Api\Security\WishlistAccessCheckerInterface;
use Malina141\SyliusWishlistPlugin\Entity\WishlistInterface;
use Malina141\SyliusWishlistPlugin\Modifier\WishlistModifierInterface;
use Malina141\SyliusWishlistPlugin\Repository\WishlistRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Channel\Repository\ChannelRepositoryInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Core\Repository\ProductVariantRepositoryInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

final class AddItemToWishlistHandlerTest extends TestCase
{
    private WishlistRepositoryInterface&MockObject $wishlistRepository;

    private ProductVariantRepositoryInterface&MockObject $productVariantRepository;

    private ChannelRepositoryInterface&MockObject $channelRepository;

    private WishlistModifierInterface&MockObject $wishlistModifier;

    private WishlistAccessCheckerInterface&MockObject $accessChecker;

    private AddItemToWishlistHandler $handler;

    protected function setUp(): void
    {
        $this->wishlistRepository = $this->createMock(WishlistRepositoryInterface::class);
        $this->productVariantRepository = $this->createMock(ProductVariantRepositoryInterface::class);
        $this->channelRepository = $this->createMock(ChannelRepositoryInterface::class);
        $this->wishlistModifier = $this->createMock(WishlistModifierInterface::class);
        $this->accessChecker = $this->createMock(WishlistAccessCheckerInterface::class);

        $this->handler = new AddItemToWishlistHandler(
            $this->wishlistRepository,
            $this->productVariantRepository,
            $this->channelRepository,
            $this->wishlistModifier,
            $this->accessChecker,
        );
    }

    public function test_it_denies_inaccessible_wishlist_before_validating_variant(): void
    {
        $channel = $this->createMock(ChannelInterface::class);
        $wishlist = $this->createMock(WishlistInterface::class);

        $this->channelRepository
            ->expects($this->once())
            ->method('findOneByCode')
            ->with('FASHION_WEB')
            ->willReturn($channel)
        ;
        $this->wishlistRepository
            ->expects($this->once())
            ->method('findOneByTokenAndChannel')
            ->with('wishlist-token', $channel)
            ->willReturn($wishlist)
        ;
        $this->accessChecker
            ->expects($this->once())
            ->method('canAccessPrivateToken')
            ->with($wishlist)
            ->willReturn(false)
        ;
        $this->productVariantRepository->expects($this->never())->method('findOneBy');
        $this->wishlistModifier->expects($this->never())->method('addVariant');

        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('Wishlist not found');

        ($this->handler)(new AddItemToWishlist('wishlist-token', 'UNKNOWN_PRODUCT_VARIANT', 'FASHION_WEB'));
    }

    public function test_it_rejects_variant_when_product_is_disabled(): void
    {
        $channel = $this->createMock(ChannelInterface::class);
        $wishlist = $this->createMock(WishlistInterface::class);
        $product = $this->createMock(ProductInterface::class);
        $productVariant = $this->createMock(ProductVariantInterface::class);

        $this->channelRepository->method('findOneByCode')->willReturn($channel);
        $this->wishlistRepository->method('findOneByTokenAndChannel')->willReturn($wishlist);
        $this->accessChecker->method('canAccessPrivateToken')->willReturn(true);

        $this->productVariantRepository
            ->method('findOneBy')
            ->with(['code' => 'DISABLED_PROD_VARIANT', 'enabled' => true])
            ->willReturn($productVariant)
        ;

        $product->method('isEnabled')->willReturn(false);
        $product->method('getCode')->willReturn('DISABLED_PROD');
        $productVariant->method('getProduct')->willReturn($product);

        $this->wishlistModifier->expects($this->never())->method('addVariant');

        $this->expectException(NotFoundHttpException::class);

        ($this->handler)(new AddItemToWishlist('wishlist-token', 'DISABLED_PROD_VARIANT', 'FASHION_WEB'));
    }

    public function test_it_rejects_variant_when_product_is_not_in_channel(): void
    {
        $channel = $this->createMock(ChannelInterface::class);
        $channel->method('getCode')->willReturn('OTHER_CHANNEL');
        $wishlist = $this->createMock(WishlistInterface::class);
        $product = $this->createMock(ProductInterface::class);
        $productVariant = $this->createMock(ProductVariantInterface::class);

        $this->channelRepository->method('findOneByCode')->willReturn($channel);
        $this->wishlistRepository->method('findOneByTokenAndChannel')->willReturn($wishlist);
        $this->accessChecker->method('canAccessPrivateToken')->willReturn(true);

        $this->productVariantRepository
            ->method('findOneBy')
            ->with(['code' => 'WRONG_CHANNEL_VARIANT', 'enabled' => true])
            ->willReturn($productVariant)
        ;

        $product->method('isEnabled')->willReturn(true);
        $product->method('getCode')->willReturn('WRONG_CHANNEL_PROD');
        $product->method('hasChannel')->with($channel)->willReturn(false);
        $productVariant->method('getProduct')->willReturn($product);

        $this->wishlistModifier->expects($this->never())->method('addVariant');

        $this->expectException(NotFoundHttpException::class);

        ($this->handler)(new AddItemToWishlist('wishlist-token', 'WRONG_CHANNEL_VARIANT', 'OTHER_CHANNEL'));
    }
}
