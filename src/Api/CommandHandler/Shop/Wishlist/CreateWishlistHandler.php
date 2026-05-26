<?php

declare(strict_types=1);

namespace Malina141\SyliusWishlistPlugin\Api\CommandHandler\Shop\Wishlist;

use Doctrine\Persistence\ObjectManager;
use Malina141\SyliusWishlistPlugin\Api\Command\Shop\Wishlist\CreateWishlist;
use Malina141\SyliusWishlistPlugin\Entity\WishlistInterface;
use Malina141\SyliusWishlistPlugin\Generator\WishlistTokenGeneratorInterface;
use Sylius\Component\Channel\Model\ChannelInterface;
use Sylius\Component\Channel\Repository\ChannelRepositoryInterface;
use Sylius\Resource\Factory\FactoryInterface;
use Webmozart\Assert\Assert;

final readonly class CreateWishlistHandler
{
    /**
     * @param FactoryInterface<WishlistInterface> $wishlistFactory
     */
    public function __construct(
        private ChannelRepositoryInterface $channelRepository,
        private FactoryInterface $wishlistFactory,
        private ObjectManager $wishlistManager,
        private WishlistTokenGeneratorInterface $wishlistTokenGenerator,
    ) {
    }

    public function __invoke(CreateWishlist $createWishlist): WishlistInterface
    {
        $channel = $this->channelRepository->findOneByCode($createWishlist->channelCode);
        Assert::isInstanceOf($channel, ChannelInterface::class);

        /** @var WishlistInterface $wishlist */
        $wishlist = $this->wishlistFactory->createNew();
        $wishlist->setChannel($channel);
        $wishlist->setToken($this->wishlistTokenGenerator->generate());

        $this->wishlistManager->persist($wishlist);
        $this->wishlistManager->flush();

        return $wishlist;
    }
}
