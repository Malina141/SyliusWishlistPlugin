<?php

declare(strict_types=1);

namespace Malina141\SyliusWishlistPlugin\Merger;

use Doctrine\ORM\EntityManagerInterface;
use Malina141\SyliusWishlistPlugin\Entity\WishlistInterface;
use Malina141\SyliusWishlistPlugin\Repository\WishlistRepositoryInterface;
use Sylius\Component\Core\Model\ShopUserInterface;

final readonly class WishlistMerger implements WishlistMergerInterface
{
    public function __construct(
        private WishlistRepositoryInterface $wishlistRepository,
        private EntityManagerInterface $wishlistManager,
    ) {
    }

    public function merge(ShopUserInterface $user, WishlistInterface $guestWishlist): void
    {
        $channel = $guestWishlist->getChannel();
        if (null === $channel) {
            return;
        }

        $userWishlist = $this->wishlistRepository->findOneByOwnerAndChannel($user, $channel);

        if (!$userWishlist instanceof WishlistInterface) {
            $guestWishlist->setOwner($user);

            return;
        }

        $this->mergeItems($guestWishlist, $userWishlist);

        $this->wishlistManager->remove($guestWishlist);
    }

    private function mergeItems(WishlistInterface $guestWishlist, WishlistInterface $userWishlist): void
    {
        foreach ($guestWishlist->getItems() as $item) {
            $variant = $item->getProductVariant();

            if (null === $variant || $userWishlist->hasProductVariant($variant)) {
                continue;
            }

            $item->setWishlist($userWishlist);
        }
    }
}
