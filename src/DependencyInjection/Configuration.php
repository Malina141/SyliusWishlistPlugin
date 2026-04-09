<?php

declare(strict_types=1);

namespace Malina141\SyliusWishlistPlugin\DependencyInjection;

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

        return $treeBuilder;
    }
}
