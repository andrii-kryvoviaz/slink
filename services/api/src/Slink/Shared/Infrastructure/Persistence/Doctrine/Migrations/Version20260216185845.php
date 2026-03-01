<?php

declare(strict_types=1);

namespace Slink\Shared\Infrastructure\Persistence\Doctrine\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260216185845 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create oauth_link and oauth_provider tables';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE "oauth_link" (id VARCHAR(36) NOT NULL, user_id VARCHAR(36) NOT NULL, provider_slug VARCHAR(50) NOT NULL, provider_user_id VARCHAR(255) NOT NULL, provider_email VARCHAR(255) DEFAULT NULL, linked_at DATETIME NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX idx_oauth_link_user ON "oauth_link" (user_id)');
        $this->addSql('CREATE UNIQUE INDEX uniq_oauth_link_provider ON "oauth_link" (provider_slug, provider_user_id)');
        $this->addSql('CREATE TABLE "oauth_provider" (id VARCHAR(36) NOT NULL, name VARCHAR(100) NOT NULL, slug VARCHAR(50) NOT NULL, type VARCHAR(50) NOT NULL, client_id VARCHAR(255) NOT NULL, client_secret CLOB NOT NULL, discovery_url VARCHAR(500) NOT NULL, scopes VARCHAR(255) NOT NULL, enabled BOOLEAN NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX uniq_oauth_provider_slug ON "oauth_provider" (slug)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE "oauth_link"');
        $this->addSql('DROP TABLE "oauth_provider"');
    }
}
