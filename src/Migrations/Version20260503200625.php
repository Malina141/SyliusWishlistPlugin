<?php

declare(strict_types=1);

namespace Malina141\SyliusWishlistPlugin\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260503200625 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'add channel_id for wishlist';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE malina141_wishlist ADD channel_id INT NOT NULL');
        $this->addSql('ALTER TABLE malina141_wishlist ADD CONSTRAINT FK_7D12510672F5A1AA FOREIGN KEY (channel_id) REFERENCES sylius_channel (id)');
        $this->addSql('CREATE UNIQUE INDEX UNQ_wishlist_owner_channel ON malina141_wishlist (owner_id, channel_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE malina141_wishlist DROP FOREIGN KEY FK_7D12510672F5A1AA');
        $this->addSql('DROP INDEX UNQ_wishlist_owner_channel ON malina141_wishlist');
        $this->addSql('ALTER TABLE malina141_wishlist DROP channel_id');
    }
}
