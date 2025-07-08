<?php

declare(strict_types=1);

namespace Slink\Shared\Infrastructure\Persistence\Doctrine\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250708141349 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE "api_key" (key_id VARCHAR(36) NOT NULL, user_id CHAR(36) NOT NULL --(DC2Type:uuid)
        , key_hash VARCHAR(255) NOT NULL, name VARCHAR(100) NOT NULL, created_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , expires_at DATETIME DEFAULT NULL --(DC2Type:datetime_immutable)
        , last_used_at DATETIME DEFAULT NULL --(DC2Type:datetime_immutable)
        , PRIMARY KEY(key_id))');
        $this->addSql('CREATE INDEX idx_api_key_hash ON "api_key" (key_hash)');
        $this->addSql('CREATE INDEX idx_api_key_user ON "api_key" (user_id)');
        $this->addSql('CREATE INDEX idx_api_key_created_at ON "api_key" (created_at)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE "api_key"');
    }
}
