<?php

declare(strict_types=1);

namespace Slink\Shared\Infrastructure\Persistence\Doctrine\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251204173457 extends AbstractMigration {
  public function getDescription(): string {
    return 'Create bookmark table and add bookmark_count to image table';
  }

  public function up(Schema $schema): void {
    $this->addSql('CREATE TABLE "bookmark" (uuid CHAR(36) NOT NULL --(DC2Type:uuid)
        , user_id CHAR(36) NOT NULL --(DC2Type:uuid)
        , image_id CHAR(36) NOT NULL --(DC2Type:uuid)
        , created_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , PRIMARY KEY(uuid), CONSTRAINT FK_DA62921DA76ED395 FOREIGN KEY (user_id) REFERENCES "user" (uuid) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_DA62921D3DA5256D FOREIGN KEY (image_id) REFERENCES "image" (uuid) NOT DEFERRABLE INITIALLY IMMEDIATE)');
    $this->addSql('CREATE INDEX IDX_DA62921DA76ED395 ON "bookmark" (user_id)');
    $this->addSql('CREATE INDEX idx_bookmark_user_created ON "bookmark" (user_id, created_at)');
    $this->addSql('CREATE INDEX idx_bookmark_image ON "bookmark" (image_id)');
    $this->addSql('CREATE UNIQUE INDEX unique_user_image_bookmark ON "bookmark" (user_id, image_id)');
    $this->addSql('ALTER TABLE image ADD COLUMN bookmark_count INTEGER DEFAULT 0 NOT NULL');
  }

  public function down(Schema $schema): void {
    $this->addSql('DROP TABLE "bookmark"');
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
  }
}
