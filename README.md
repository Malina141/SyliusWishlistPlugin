# Sylius Wishlist Plugin

Wishlist plugin for Sylius 2 applications.

## Features

- Shop wishlist page for the current customer or guest wishlist.
- Add-to-wishlist buttons for product list and product details pages.
- Wishlist sharing and unsharing.
- Bulk wishlist item deletion.
- Bulk add-to-cart from wishlist.
- Admin wishlist grid and wishlist details view.
- Shop API resource for wishlist operations.

## Compatibility

Sylius version | Symfony version | PHP version |
 --- | --- | --- |
^2.1 | ^6.4 or ^7.4 | ^8.3 |

## Manual Installation

### 1. Install the Plugin via Composer

```bash
composer require malina141/sylius-wishlist-plugin
```

### 2. Enable the Bundle

Symfony Flex should add this entry automatically. Check `config/bundles.php` after installation and add it manually if it is missing:

```php
// config/bundles.php

return [
    // ...
    Malina141\SyliusWishlistPlugin\Malina141SyliusWishlistPlugin::class => ['all' => true],
];
```

### 3. Import Configuration

```yaml
# config/packages/_sylius.yaml

imports:
    # ...
    - { resource: "@Malina141SyliusWishlistPlugin/config/config.yaml" }
```

This imports the plugin Twig hooks, grids, and workflow metadata.

### 4. Import routes

```yaml
# config/routes.yaml

# ...
malina141_sylius_wishlist:
    resource: "@Malina141SyliusWishlistPlugin/config/routes.yaml"
```

### 5. Register frontend controllers

On Sylius Standard 2.1+ with Symfony Flex enabled, the plugin frontend package should get registered automatically by Symfony UX package synchronization.

The generated controller configuration should contain:

```json
{
    "controllers": {
        "@malina141/sylius-wishlist-plugin": {
            "clipboard-copy": {
                "enabled": true,
                "fetch": "lazy"
            },
            "wishlist-bulk-actions": {
                "enabled": true,
                "fetch": "lazy"
            }
        }
    },
    "entrypoints": []
}
```

If your application does not use Flex package synchronization, install the frontend package manually:

```bash
yarn add @malina141/sylius-wishlist-plugin@file:vendor/malina141/sylius-wishlist-plugin/assets
```

Then add the controller block above to the controllers file used by your shop Stimulus bridge. In Sylius Standard 2.1+ this is usually `assets/controllers.json`; in Sylius 2.0 it may be `assets/shop/controllers.json`.

Sylius Standard 2.0 uses a separate Sylius shop asset build that starts the shop Stimulus application. To register the wishlist controllers in that existing application, point the Sylius shop webpack config at your shop controllers file:

```js
// webpack.config.js

const shopConfig = SyliusShop.getWebpackConfig(path.resolve(__dirname));

shopConfig.resolve.alias['@symfony/stimulus-bridge/controllers.json'] = path.resolve(__dirname, './assets/shop/controllers.json');
```

### 6. Install and build assets

```bash
bin/console assets:install

yarn install --force
yarn encore dev # or yarn encore prod
```

### 7. Run Doctrine Migrations

The plugin comes with database changes. Check the migrations and then run:

```bash
bin/console doctrine:migrations:migrate # add `-e prod` for production
```

### 8. Clear the Symfony Cache

Finally, clear the Symfony cache to ensure changes are applied:

```bash
bin/console cache:clear
```

## Docker Installation

```bash
docker compose exec php composer require malina141/sylius-wishlist-plugin
docker compose exec php bin/console assets:install
docker compose run --rm --entrypoint yarn nodejs install --force
docker compose run --rm --entrypoint yarn nodejs encore dev
docker compose exec php bin/console doctrine:migrations:migrate
docker compose exec php bin/console cache:clear
```

Adjust service names if your project does not use `php` and `nodejs`.

## Configuration

Default configuration can be inspected with:

```bash
bin/console config:dump-reference malina141_sylius_wishlist
```

Example override:

```yaml
malina141_sylius_wishlist:
    cookie:
        secure: true
    bulk_add_to_cart:
        redirect_route: sylius_shop_cart_summary
```

