<?php

declare(strict_types=1);

namespace Malina141\SyliusWishlistPlugin\Repository;

use Doctrine\ORM\QueryBuilder;
use Malina141\SyliusWishlistPlugin\Entity\WishlistInterface;
use Malina141\SyliusWishlistPlugin\Entity\WishlistItemInterface;
use Sylius\Resource\Doctrine\Persistence\RepositoryInterface;

/**
 * @extends RepositoryInterface<WishlistItemInterface>
 */
interface WishlistItemRepositoryInterface extends RepositoryInterface
{
    public function createShopWishlistItemQueryBuilder(WishlistInterface $wishlist): QueryBuilder;

    public function findOneByIdAndWishlist(string|int $id, WishlistInterface $wishlist): ?WishlistItemInterface;

    /**
     * @param array<string|int> $ids
     */
    public function findByIdsAndWishlist(array $ids, WishlistInterface $wishlist): array;
}
