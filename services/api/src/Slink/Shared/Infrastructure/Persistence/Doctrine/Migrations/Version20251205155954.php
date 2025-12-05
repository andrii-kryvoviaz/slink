<?php

declare(strict_types=1);

namespace Slink\Shared\Infrastructure\Persistence\Doctrine\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251205155954 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE "share" (uuid CHAR(36) NOT NULL --(DC2Type:uuid)
        , image_id CHAR(36) NOT NULL --(DC2Type:uuid)
        , target_url VARCHAR(2048) NOT NULL, created_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , PRIMARY KEY(uuid), CONSTRAINT FK_EF069D5A3DA5256D FOREIGN KEY (image_id) REFERENCES "image" (uuid) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX idx_share_image ON "share" (image_id)');
        $this->addSql('CREATE INDEX idx_share_target_url ON "share" (target_url)');
        $this->addSql('CREATE TABLE "short_url" (uuid CHAR(36) NOT NULL --(DC2Type:uuid)
        , share_id CHAR(36) NOT NULL --(DC2Type:uuid)
        , short_code VARCHAR(8) NOT NULL, PRIMARY KEY(uuid), CONSTRAINT FK_833605312AE63FDB FOREIGN KEY (share_id) REFERENCES "share" (uuid) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_833605312AE63FDB ON "short_url" (share_id)');
        $this->addSql('CREATE UNIQUE INDEX unique_short_code ON "short_url" (short_code)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE "share"');
        $this->addSql('DROP TABLE "short_url"');
    }
}
