<?php

declare(strict_types=1);

namespace Slink\Shared\Infrastructure\Persistence\Doctrine\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260301000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add sort_order column to oauth_provider table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE "oauth_provider" ADD COLUMN sort_order DOUBLE PRECISION NOT NULL DEFAULT 0');
        $this->addSql('UPDATE "oauth_provider" SET sort_order = (SELECT COUNT(*) FROM "oauth_provider" AS p2 WHERE p2.id < "oauth_provider".id) + 1.0');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE "oauth_provider" DROP COLUMN sort_order');
    }
}
