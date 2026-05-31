<?php

declare(strict_types=1);

namespace Malina141\SyliusWishlistPlugin\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Sylius\Bundle\CoreBundle\Doctrine\Migrations\AbstractMigration;

final class Version20260531170241 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'malina141_wishlist_plugin_mysql_migrations';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE malina141_wishlist (
              id INT AUTO_INCREMENT NOT NULL,
              owner_id INT DEFAULT NULL,
              channel_id INT NOT NULL,
              token VARCHAR(32) DEFAULT NULL,
              share_token VARCHAR(32) DEFAULT NULL,
              share_state VARCHAR(255) NOT NULL,
              name VARCHAR(255) DEFAULT NULL,
              INDEX IDX_7D1251067E3C61F9 (owner_id),
              INDEX IDX_7D12510672F5A1AA (channel_id),
              UNIQUE INDEX UNQ_wishlist_owner_channel (owner_id, channel_id),
              UNIQUE INDEX UNQ_wishlist_token_channel (token, channel_id),
              UNIQUE INDEX UNQ_wishlist_share_token_channel (share_token, channel_id),
              PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE malina141_wishlist_item (
              id INT AUTO_INCREMENT NOT NULL,
              wishlist_id INT NOT NULL,
              product_variant_id INT NOT NULL,
              INDEX IDX_3BFCB8C7FB8E54CD (wishlist_id),
              INDEX IDX_3BFCB8C7A80EF684 (product_variant_id),
              UNIQUE INDEX malina141_wishlist_item_unique_variant_idx (wishlist_id, product_variant_id),
              PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              malina141_wishlist
            ADD
              CONSTRAINT FK_7D1251067E3C61F9 FOREIGN KEY (owner_id) REFERENCES sylius_shop_user (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              malina141_wishlist
            ADD
              CONSTRAINT FK_7D12510672F5A1AA FOREIGN KEY (channel_id) REFERENCES sylius_channel (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              malina141_wishlist_item
            ADD
              CONSTRAINT FK_3BFCB8C7FB8E54CD FOREIGN KEY (wishlist_id) REFERENCES malina141_wishlist (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              malina141_wishlist_item
            ADD
              CONSTRAINT FK_3BFCB8C7A80EF684 FOREIGN KEY (product_variant_id) REFERENCES sylius_product_variant (id) ON DELETE CASCADE
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE malina141_wishlist DROP FOREIGN KEY FK_7D1251067E3C61F9');
        $this->addSql('ALTER TABLE malina141_wishlist DROP FOREIGN KEY FK_7D12510672F5A1AA');
        $this->addSql('ALTER TABLE malina141_wishlist_item DROP FOREIGN KEY FK_3BFCB8C7FB8E54CD');
        $this->addSql('ALTER TABLE malina141_wishlist_item DROP FOREIGN KEY FK_3BFCB8C7A80EF684');
        $this->addSql('DROP TABLE malina141_wishlist');
        $this->addSql('DROP TABLE malina141_wishlist_item');
    }
}
