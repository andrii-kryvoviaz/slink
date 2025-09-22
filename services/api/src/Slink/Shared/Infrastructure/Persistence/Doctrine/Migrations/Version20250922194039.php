<?php

declare(strict_types=1);

namespace Slink\Shared\Infrastructure\Persistence\Doctrine\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250922194039 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE image_to_tag (image_id CHAR(36) NOT NULL --(DC2Type:uuid)
        , tag_id CHAR(36) NOT NULL --(DC2Type:uuid)
        , PRIMARY KEY(image_id, tag_id), CONSTRAINT FK_FAA821453DA5256D FOREIGN KEY (image_id) REFERENCES "image" (uuid) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_FAA82145BAD26311 FOREIGN KEY (tag_id) REFERENCES "tag" (uuid) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_FAA821453DA5256D ON image_to_tag (image_id)');
        $this->addSql('CREATE INDEX IDX_FAA82145BAD26311 ON image_to_tag (tag_id)');
        $this->addSql('CREATE TABLE "tag" (uuid CHAR(36) NOT NULL --(DC2Type:uuid)
        , user_id VARCHAR(36) NOT NULL, parent_id VARCHAR(36) DEFAULT NULL, name VARCHAR(50) NOT NULL, path CLOB NOT NULL, PRIMARY KEY(uuid), CONSTRAINT FK_389B783A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (uuid) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_389B783727ACA70 FOREIGN KEY (parent_id) REFERENCES "tag" (uuid) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_389B783A76ED395 ON "tag" (user_id)');
        $this->addSql('CREATE INDEX IDX_389B783727ACA70 ON "tag" (parent_id)');
        $this->addSql('CREATE INDEX idx_tag_user_name ON "tag" (user_id, name)');
        $this->addSql('CREATE INDEX idx_tag_user_parent ON "tag" (user_id, parent_id)');
        $this->addSql('CREATE INDEX idx_tag_user_path ON "tag" (user_id, path)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__image AS SELECT uuid, user_id, file_name, description, is_public, created_at, updated_at, views, size, mime_type, width, height, sha1_hash FROM image');
        $this->addSql('DROP TABLE image');
        $this->addSql('CREATE TABLE image (uuid CHAR(36) NOT NULL --(DC2Type:uuid)
        , user_id CHAR(36) DEFAULT NULL --(DC2Type:uuid)
        , file_name VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, is_public BOOLEAN NOT NULL, created_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , updated_at DATETIME DEFAULT NULL --(DC2Type:datetime_immutable)
        , views INTEGER NOT NULL, size INTEGER NOT NULL, mime_type VARCHAR(255) NOT NULL, width INTEGER NOT NULL, height INTEGER NOT NULL, sha1_hash VARCHAR(40) DEFAULT NULL, PRIMARY KEY(uuid), CONSTRAINT FK_C53D045FA76ED395 FOREIGN KEY (user_id) REFERENCES user (uuid) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO image (uuid, user_id, file_name, description, is_public, created_at, updated_at, views, size, mime_type, width, height, sha1_hash) SELECT uuid, user_id, file_name, description, is_public, created_at, updated_at, views, size, mime_type, width, height, sha1_hash FROM __temp__image');
        $this->addSql('DROP TABLE __temp__image');
        $this->addSql('CREATE INDEX IDX_C53D045FA76ED395 ON image (user_id)');
        $this->addSql('CREATE INDEX idx_image_user_created_at ON image (user_id, created_at)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE image_to_tag');
        $this->addSql('DROP TABLE "tag"');
        $this->addSql('CREATE TEMPORARY TABLE __temp__image AS SELECT uuid, user_id, file_name, description, is_public, created_at, updated_at, views, size, mime_type, width, height, sha1_hash FROM "image"');
        $this->addSql('DROP TABLE "image"');
        $this->addSql('CREATE TABLE "image" (uuid CHAR(36) NOT NULL --(DC2Type:uuid)
        , user_id CHAR(36) DEFAULT NULL --(DC2Type:uuid)
        , file_name VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, is_public BOOLEAN NOT NULL, created_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , updated_at DATETIME DEFAULT NULL --(DC2Type:datetime_immutable)
        , views INTEGER NOT NULL, size INTEGER NOT NULL, mime_type VARCHAR(255) NOT NULL, width INTEGER NOT NULL, height INTEGER NOT NULL, sha1_hash VARCHAR(40) DEFAULT NULL, PRIMARY KEY(uuid), CONSTRAINT FK_C53D045FA76ED395 FOREIGN KEY (user_id) REFERENCES "user" (uuid) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO "image" (uuid, user_id, file_name, description, is_public, created_at, updated_at, views, size, mime_type, width, height, sha1_hash) SELECT uuid, user_id, file_name, description, is_public, created_at, updated_at, views, size, mime_type, width, height, sha1_hash FROM __temp__image');
        $this->addSql('DROP TABLE __temp__image');
        $this->addSql('CREATE INDEX IDX_C53D045FA76ED395 ON "image" (user_id)');
        $this->addSql('CREATE INDEX idx_image_user_created_at ON "image" (user_id, created_at)');
        $this->addSql('CREATE INDEX idx_image_sha1_hash ON "image" (sha1_hash)');
    }
}
