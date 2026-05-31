<?php

declare(strict_types=1);

namespace Malina141\SyliusWishlistPlugin\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Sylius\Bundle\CoreBundle\Doctrine\Migrations\AbstractPostgreSQLMigration;

final class Version20260531171449 extends AbstractPostgreSQLMigration
{
    public function getDescription(): string
    {
        return 'malina141_wishlist_plugin_pgsql_migrations';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE SEQUENCE malina141_wishlist_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE malina141_wishlist_item_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql(<<<'SQL'
            CREATE TABLE malina141_wishlist (
              id INT NOT NULL,
              owner_id INT DEFAULT NULL,
              channel_id INT NOT NULL,
              token VARCHAR(32) DEFAULT NULL,
              share_token VARCHAR(32) DEFAULT NULL,
              share_state VARCHAR(255) NOT NULL,
              name VARCHAR(255) DEFAULT NULL,
              PRIMARY KEY(id)
            )
        SQL);
        $this->addSql('CREATE INDEX IDX_7D1251067E3C61F9 ON malina141_wishlist (owner_id)');
        $this->addSql('CREATE INDEX IDX_7D12510672F5A1AA ON malina141_wishlist (channel_id)');
        $this->addSql('CREATE UNIQUE INDEX UNQ_wishlist_owner_channel ON malina141_wishlist (owner_id, channel_id)');
        $this->addSql('CREATE UNIQUE INDEX UNQ_wishlist_token_channel ON malina141_wishlist (token, channel_id)');
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNQ_wishlist_share_token_channel ON malina141_wishlist (share_token, channel_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE malina141_wishlist_item (
              id INT NOT NULL,
              wishlist_id INT NOT NULL,
              product_variant_id INT NOT NULL,
              PRIMARY KEY(id)
            )
        SQL);
        $this->addSql('CREATE INDEX IDX_3BFCB8C7FB8E54CD ON malina141_wishlist_item (wishlist_id)');
        $this->addSql('CREATE INDEX IDX_3BFCB8C7A80EF684 ON malina141_wishlist_item (product_variant_id)');
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX malina141_wishlist_item_unique_variant_idx ON malina141_wishlist_item (wishlist_id, product_variant_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              malina141_wishlist
            ADD
              CONSTRAINT FK_7D1251067E3C61F9 FOREIGN KEY (owner_id) REFERENCES sylius_shop_user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              malina141_wishlist
            ADD
              CONSTRAINT FK_7D12510672F5A1AA FOREIGN KEY (channel_id) REFERENCES sylius_channel (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              malina141_wishlist_item
            ADD
              CONSTRAINT FK_3BFCB8C7FB8E54CD FOREIGN KEY (wishlist_id) REFERENCES malina141_wishlist (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              malina141_wishlist_item
            ADD
              CONSTRAINT FK_3BFCB8C7A80EF684 FOREIGN KEY (product_variant_id) REFERENCES sylius_product_variant (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP SEQUENCE malina141_wishlist_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE malina141_wishlist_item_id_seq CASCADE');
        $this->addSql('ALTER TABLE malina141_wishlist DROP CONSTRAINT FK_7D1251067E3C61F9');
        $this->addSql('ALTER TABLE malina141_wishlist DROP CONSTRAINT FK_7D12510672F5A1AA');
        $this->addSql('ALTER TABLE malina141_wishlist_item DROP CONSTRAINT FK_3BFCB8C7FB8E54CD');
        $this->addSql('ALTER TABLE malina141_wishlist_item DROP CONSTRAINT FK_3BFCB8C7A80EF684');
        $this->addSql('DROP TABLE malina141_wishlist');
        $this->addSql('DROP TABLE malina141_wishlist_item');
    }
}
