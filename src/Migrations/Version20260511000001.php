<?php

declare(strict_types=1);

namespace Malina141\SyliusWishlistPlugin\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Sylius\Bundle\CoreBundle\Doctrine\Migrations\AbstractPostgreSQLMigration;

final class Version20260511000001 extends AbstractPostgreSQLMigration
{
    public function getDescription(): string
    {
        return 'add share_token and share_state columns to wishlist table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE malina141_wishlist ADD share_token VARCHAR(32) DEFAULT NULL');
        $this->addSql('ALTER TABLE malina141_wishlist ADD share_state VARCHAR(255) NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNQ_wishlist_share_token_channel ON malina141_wishlist (share_token, channel_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX UNQ_wishlist_share_token_channel');
        $this->addSql('ALTER TABLE malina141_wishlist DROP share_token');
        $this->addSql('ALTER TABLE malina141_wishlist DROP share_state');
    }
}
