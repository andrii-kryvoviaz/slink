<?php

declare(strict_types=1);

namespace Slink\Shared\Infrastructure\Persistence\Doctrine\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260417001419 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add expires_at column to share table for ShareAccessControl';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE "share" ADD COLUMN expires_at DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE "share" DROP COLUMN expires_at');
    }
}
