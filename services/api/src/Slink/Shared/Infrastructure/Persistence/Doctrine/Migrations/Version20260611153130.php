<?php

declare(strict_types=1);

namespace Slink\Shared\Infrastructure\Persistence\Doctrine\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260611153130 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add registration_policy and approval_policy columns to oauth_provider table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE \"oauth_provider\" ADD COLUMN registration_policy VARCHAR(20) NOT NULL DEFAULT 'inherit'");
        $this->addSql("ALTER TABLE \"oauth_provider\" ADD COLUMN approval_policy VARCHAR(20) NOT NULL DEFAULT 'inherit'");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE "oauth_provider" DROP COLUMN registration_policy');
        $this->addSql('ALTER TABLE "oauth_provider" DROP COLUMN approval_policy');
    }
}
