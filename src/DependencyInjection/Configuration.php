<?php

declare(strict_types=1);

namespace Malina141\SyliusWishlistPlugin\DependencyInjection;

use Malina141\SyliusWishlistPlugin\Entity\Wishlist;
use Malina141\SyliusWishlistPlugin\Entity\WishlistInterface;
use Malina141\SyliusWishlistPlugin\Entity\WishlistItem;
use Malina141\SyliusWishlistPlugin\Entity\WishlistItemInterface;
use Malina141\SyliusWishlistPlugin\Repository\WishlistItemRepository;
use Malina141\SyliusWishlistPlugin\Repository\WishlistRepository;
use Sylius\Bundle\ResourceBundle\Controller\ResourceController;
use Sylius\Component\Resource\Factory\Factory;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    /**
     * @psalm-suppress UnusedVariable
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('malina141_sylius_wishlist');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->arrayNode('cookie')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('name')->defaultValue('malina141_wishlist_token')->cannotBeEmpty()->end()
                        ->integerNode('lifetime')->defaultValue(31536000)->end()
                        ->scalarNode('path')->defaultValue('/')->cannotBeEmpty()->end()
                        ->booleanNode('secure')->defaultTrue()->end()
                        ->booleanNode('http_only')->defaultTrue()->end()
                        ->enumNode('same_site')->values(['lax', 'strict', 'none', null])->defaultValue('lax')->end()
                    ->end()
                ->end()
                ->arrayNode('token')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->integerNode('length')->defaultValue(32)->min(16)->max(32)->end()
                    ->end()
                ->end()
                ->arrayNode('share_token')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->integerNode('length')->defaultValue(32)->min(16)->max(32)->end()
                    ->end()
                ->end()
                ->arrayNode('bulk_add_to_cart')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('redirect_route')->defaultValue('sylius_shop_cart_summary')->cannotBeEmpty()->end()
                        ->scalarNode('csrf_token_id')->defaultValue('bulk_wishlist_add_to_cart')->cannotBeEmpty()->end()
                    ->end()
                ->end()
                ->arrayNode('resources')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('wishlist')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->variableNode('options')->end()
                                ->arrayNode('classes')
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                        ->scalarNode('model')->defaultValue(Wishlist::class)->cannotBeEmpty()->end()
                                        ->scalarNode('interface')->defaultValue(WishlistInterface::class)->cannotBeEmpty()->end()
                                        ->scalarNode('repository')->defaultValue(WishlistRepository::class)->cannotBeEmpty()->end()
                                        ->scalarNode('controller')->defaultValue(ResourceController::class)->cannotBeEmpty()->end()
                                        ->scalarNode('factory')->defaultValue(Factory::class)->cannotBeEmpty()->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('wishlist_item')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->variableNode('options')->end()
                                ->arrayNode('classes')
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                        ->scalarNode('model')->defaultValue(WishlistItem::class)->cannotBeEmpty()->end()
                                        ->scalarNode('interface')->defaultValue(WishlistItemInterface::class)->cannotBeEmpty()->end()
                                        ->scalarNode('repository')->defaultValue(WishlistItemRepository::class)->cannotBeEmpty()->end()
                                        ->scalarNode('controller')->defaultValue(ResourceController::class)->cannotBeEmpty()->end()
                                        ->scalarNode('factory')->defaultValue(Factory::class)->cannotBeEmpty()->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
