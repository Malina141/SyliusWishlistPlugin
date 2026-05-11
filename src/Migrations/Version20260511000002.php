<?php

declare(strict_types=1);

namespace Malina141\SyliusWishlistPlugin\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Sylius\Bundle\CoreBundle\Doctrine\Migrations\AbstractMigration;

final class Version20260511000002 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add name column to wishlist table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE malina141_wishlist ADD name VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE malina141_wishlist DROP name');
    }
}
