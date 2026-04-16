<?php

declare(strict_types=1);

namespace Slink\Shared\Infrastructure\Persistence\Doctrine\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260416155323 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add is_published column to share table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE "share" ADD COLUMN is_published BOOLEAN NOT NULL DEFAULT 0');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE "share" DROP COLUMN is_published');
    }
}
