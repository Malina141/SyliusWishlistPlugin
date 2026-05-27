<?php

declare(strict_types=1);

namespace Malina141\SyliusWishlistPlugin\Api\StateProcessor\Shop\Wishlist;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Doctrine\Persistence\ObjectManager;
use Malina141\SyliusWishlistPlugin\Entity\WishlistInterface;
use Malina141\SyliusWishlistPlugin\Modifier\WishlistModifierInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Core\Repository\ProductVariantRepositoryInterface;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Webmozart\Assert\Assert;

/** @implements ProcessorInterface<WishlistInterface, void> */
final readonly class RemoveItemFromWishlistProcessor implements ProcessorInterface
{
    public function __construct(
        private ProductVariantRepositoryInterface $productVariantRepository,
        private WishlistModifierInterface $wishlistModifier,
        private ObjectManager $wishlistManager,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): void
    {
        Assert::keyExists($uriVariables, 'variantCode');
        Assert::string($uriVariables['variantCode']);

        $productVariant = $this->productVariantRepository->findOneBy(['code' => $uriVariables['variantCode']]);
        if (!$productVariant instanceof ProductVariantInterface) {
            throw new UnprocessableEntityHttpException(\sprintf('Product variant with code "%s" was not found', $uriVariables['variantCode']));
        }

        $this->wishlistModifier->removeVariant($data, $productVariant);

        $this->wishlistManager->flush();
    }
}
