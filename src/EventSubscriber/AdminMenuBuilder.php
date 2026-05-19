<?php

declare(strict_types=1);

namespace Malina141\SyliusWishlistPlugin\EventSubscriber;

use Sylius\Bundle\UiBundle\Menu\Event\MenuBuilderEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class AdminMenuBuilder implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            'sylius.menu.admin.main' => 'addWishlistMenuItem',
        ];
    }

    public function addWishlistMenuItem(MenuBuilderEvent $event): void
    {
        $menu = $event->getMenu();
        $customers = $menu->getChild('customers');

        if (null === $customers) {
            return;
        }

        $customers
            ->addChild('wishlists', [
                'route' => 'malina141_sylius_wishlist_admin_wishlist_index',
                'extras' => ['routes' => [
                    ['route' => 'malina141_sylius_wishlist_admin_wishlist_show'],
                ]],
            ])
            ->setLabel('sylius_wishlist.ui.wishlists')
            ->setLabelAttribute('icon', 'tabler:heart')
        ;
    }
}
