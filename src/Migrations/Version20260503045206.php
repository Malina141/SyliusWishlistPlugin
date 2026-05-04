<?php

declare(strict_types=1);

namespace Malina141\SyliusWishlistPlugin\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Sylius\Bundle\CoreBundle\Doctrine\Migrations\AbstractMigration;

final class Version20260503045206 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'add wishlist and wishlist_item tables';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE malina141_wishlist (id INT AUTO_INCREMENT NOT NULL, owner_id INT DEFAULT NULL, INDEX IDX_7D1251067E3C61F9 (owner_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE malina141_wishlist_item (id INT AUTO_INCREMENT NOT NULL, wishlist_id INT NOT NULL, product_variant_id INT NOT NULL, INDEX IDX_3BFCB8C7FB8E54CD (wishlist_id), INDEX IDX_3BFCB8C7A80EF684 (product_variant_id), UNIQUE INDEX malina141_wishlist_item_unique_variant_idx (wishlist_id, product_variant_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE malina141_wishlist ADD CONSTRAINT FK_7D1251067E3C61F9 FOREIGN KEY (owner_id) REFERENCES sylius_shop_user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE malina141_wishlist_item ADD CONSTRAINT FK_3BFCB8C7FB8E54CD FOREIGN KEY (wishlist_id) REFERENCES malina141_wishlist (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE malina141_wishlist_item ADD CONSTRAINT FK_3BFCB8C7A80EF684 FOREIGN KEY (product_variant_id) REFERENCES sylius_product_variant (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE malina141_wishlist DROP FOREIGN KEY FK_7D1251067E3C61F9');
        $this->addSql('ALTER TABLE malina141_wishlist_item DROP FOREIGN KEY FK_3BFCB8C7FB8E54CD');
        $this->addSql('ALTER TABLE malina141_wishlist_item DROP FOREIGN KEY FK_3BFCB8C7A80EF684');
        $this->addSql('DROP TABLE malina141_wishlist');
        $this->addSql('DROP TABLE malina141_wishlist_item');
    }
}
