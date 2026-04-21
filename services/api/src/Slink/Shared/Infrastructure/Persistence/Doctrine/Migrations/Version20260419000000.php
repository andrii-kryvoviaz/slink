<?php

declare(strict_types=1);

namespace Slink\Shared\Infrastructure\Persistence\Doctrine\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260419000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add password_hash column to share table for ShareAccessControl';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE "share" ADD COLUMN password_hash VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE "share" DROP COLUMN password_hash');
    }
}
