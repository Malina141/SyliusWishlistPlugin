<?php

declare(strict_types=1);

namespace Malina141\SyliusWishlistPlugin\Api\CommandHandler\Shop\Wishlist;

use Doctrine\Persistence\ObjectManager;
use Malina141\SyliusWishlistPlugin\Api\Command\Shop\Wishlist\CreateWishlist;
use Malina141\SyliusWishlistPlugin\Entity\WishlistInterface;
use Malina141\SyliusWishlistPlugin\Generator\WishlistTokenGeneratorInterface;
use Malina141\SyliusWishlistPlugin\Repository\WishlistRepositoryInterface;
use Sylius\Bundle\ApiBundle\Context\UserContextInterface;
use Sylius\Component\Channel\Model\ChannelInterface;
use Sylius\Component\Channel\Repository\ChannelRepositoryInterface;
use Sylius\Component\Core\Model\ShopUserInterface;
use Sylius\Resource\Factory\FactoryInterface;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
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
        private UserContextInterface $userContext,
        private WishlistRepositoryInterface $wishlistRepository,
    ) {
    }

    public function __invoke(CreateWishlist $createWishlist): WishlistInterface
    {
        $channel = $this->channelRepository->findOneByCode($createWishlist->channelCode);
        Assert::isInstanceOf($channel, ChannelInterface::class);

        $user = $this->userContext->getUser();

        /** @var WishlistInterface $wishlist */
        $wishlist = $this->wishlistFactory->createNew();
        $wishlist->setChannel($channel);
        $wishlist->setToken($this->wishlistTokenGenerator->generate());

        if ($user instanceof ShopUserInterface) {
            $this->assertWishlistIsUniqueForUser($user, $channel);
            $wishlist->setOwner($user);
        }

        $this->wishlistManager->persist($wishlist);
        $this->wishlistManager->flush();

        return $wishlist;
    }

    private function assertWishlistIsUniqueForUser(ShopUserInterface $user, ChannelInterface $channel): void
    {
        if (null !== $this->wishlistRepository->findOneByOwnerAndChannel($user, $channel)) {
            throw new ConflictHttpException('A wishlist already exists for this user in this channel.');
        }
    }
}
