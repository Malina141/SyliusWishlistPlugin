<?php

declare(strict_types=1);

namespace Malina141\SyliusWishlistPlugin\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Sylius\Bundle\CoreBundle\Doctrine\Migrations\AbstractPostgreSQLMigration;

final class Version20260506120001 extends AbstractPostgreSQLMigration
{
    public function getDescription(): string
    {
        return 'add token column to wishlist table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE malina141_wishlist ADD token VARCHAR(32) DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNQ_wishlist_token_channel ON malina141_wishlist (token, channel_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX UNQ_wishlist_token_channel');
        $this->addSql('ALTER TABLE malina141_wishlist DROP token');
    }
}
