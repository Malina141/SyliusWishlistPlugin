<?php

declare(strict_types=1);

namespace Malina141\SyliusWishlistPlugin\Api\StateProvider\Shop\Wishlist;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Malina141\SyliusWishlistPlugin\Entity\WishlistInterface;
use Malina141\SyliusWishlistPlugin\Repository\WishlistRepositoryInterface;
use Sylius\Component\Channel\Context\ChannelContextInterface;
use Webmozart\Assert\Assert;

/** @implements ProviderInterface<WishlistInterface> */
final readonly class ItemProvider implements ProviderInterface
{
    public function __construct(
        private ChannelContextInterface $channelContext,
        private WishlistRepositoryInterface $wishlistRepository,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): ?WishlistInterface
    {
        Assert::keyExists($uriVariables, 'token');
        Assert::string($uriVariables['token']);

        return $this->wishlistRepository->findOneByTokenAndChannel(
            $uriVariables['token'],
            $this->channelContext->getChannel(),
        );
    }
}
