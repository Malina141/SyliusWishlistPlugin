<?php

declare(strict_types=1);

namespace Malina141\SyliusWishlistPlugin\DependencyInjection;

use Sylius\Bundle\CoreBundle\DependencyInjection\PrependDoctrineMigrationsTrait;
use Sylius\Bundle\ResourceBundle\DependencyInjection\Extension\AbstractResourceExtension;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

final class Malina141SyliusWishlistExtension extends AbstractResourceExtension implements PrependExtensionInterface
{
    use PrependDoctrineMigrationsTrait;

    /** @psalm-suppress UnusedVariable */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $config = $this->processConfiguration($this->getConfiguration([], $container), $configs);

        $container->setParameter('malina141_sylius_wishlist.cookie.name', $config['cookie']['name']);
        $container->setParameter('malina141_sylius_wishlist.cookie.lifetime', $config['cookie']['lifetime']);
        $container->setParameter('malina141_sylius_wishlist.cookie.path', $config['cookie']['path']);
        $container->setParameter('malina141_sylius_wishlist.cookie.secure', $config['cookie']['secure']);
        $container->setParameter('malina141_sylius_wishlist.cookie.http_only', $config['cookie']['http_only']);
        $container->setParameter('malina141_sylius_wishlist.cookie.same_site', $config['cookie']['same_site']);
        $container->setParameter('malina141_sylius_wishlist.token.length', $config['token']['length']);
        $container->setParameter('malina141_sylius_wishlist.share_token.length', $config['share_token']['length']);
        $container->setParameter('malina141_sylius_wishlist.bulk_add_to_cart.redirect_route', $config['bulk_add_to_cart']['redirect_route']);
        $container->setParameter('malina141_sylius_wishlist.bulk_add_to_cart.csrf_token_id', $config['bulk_add_to_cart']['csrf_token_id']);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../../config'));

        $loader->load('services.xml');
    }

    public function prepend(ContainerBuilder $container): void
    {
        $this->prependDoctrineMigrations($container);
        $this->prependApiPlatformMapping($container);

        $config = $this->getCurrentConfiguration($container);
        $this->registerResources('malina141_sylius_wishlist', 'doctrine/orm', $config['resources'], $container);
    }

    protected function getMigrationsNamespace(): string
    {
        return 'Malina141\SyliusWishlistPlugin\Migrations';
    }

    protected function getMigrationsDirectory(): string
    {
        return '@Malina141SyliusWishlistPlugin/src/Migrations';
    }

    protected function getNamespacesOfMigrationsExecutedBefore(): array
    {
        return [
            'Sylius\Bundle\CoreBundle\Migrations',
        ];
    }

    private function getCurrentConfiguration(ContainerBuilder $container): array
    {
        $configuration = $this->getConfiguration([], $container);
        $configs = $container->getExtensionConfig($this->getAlias());

        return $this->processConfiguration($configuration, $configs);
    }

    private function prependApiPlatformMapping(ContainerBuilder $container): void
    {
        /** @var array<string, array{path: string}> $bundlesMetadata */
        $bundlesMetadata = $container->getParameter('kernel.bundles_metadata');
        $path = $bundlesMetadata['Malina141SyliusWishlistPlugin']['path'] . '/config/api_platform';

        $container->prependExtensionConfig('api_platform', ['mapping' => ['paths' => [$path]]]);
    }
}
