<?php

declare(strict_types=1);

namespace Slink\Shared\Infrastructure\Persistence\Doctrine\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260423134014 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add indexes for share listing filters (is_published + created_at + uuid, expires_at)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE INDEX idx_share_listing ON "share" (is_published, created_at, uuid)');
        $this->addSql('CREATE INDEX idx_share_expires_at ON "share" (expires_at)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX idx_share_listing');
        $this->addSql('DROP INDEX idx_share_expires_at');
    }
}
