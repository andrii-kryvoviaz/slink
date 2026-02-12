<?php

declare(strict_types=1);

namespace Slink\Shared\Infrastructure\Persistence\Doctrine\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260212000000 extends AbstractMigration {
  public function getDescription(): string {
    return 'Add ON DELETE CASCADE to image_to_tag.tag_id and ON DELETE SET NULL to tag.parent_id';
  }

  public function up(Schema $schema): void {
    $this->addSql('CREATE TEMPORARY TABLE __temp__image_to_tag AS SELECT image_id, tag_id FROM image_to_tag');
    $this->addSql('DROP TABLE image_to_tag');
    $this->addSql('CREATE TABLE image_to_tag (image_id CHAR(36) NOT NULL, tag_id CHAR(36) NOT NULL, PRIMARY KEY(image_id, tag_id), CONSTRAINT FK_FAA821453DA5256D FOREIGN KEY (image_id) REFERENCES "image" (uuid) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_FAA82145BAD26311 FOREIGN KEY (tag_id) REFERENCES "tag" (uuid) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
    $this->addSql('INSERT INTO image_to_tag (image_id, tag_id) SELECT image_id, tag_id FROM __temp__image_to_tag');
    $this->addSql('DROP TABLE __temp__image_to_tag');
    $this->addSql('CREATE INDEX IDX_FAA821453DA5256D ON image_to_tag (image_id)');
    $this->addSql('CREATE INDEX IDX_FAA82145BAD26311 ON image_to_tag (tag_id)');

    $this->addSql('CREATE TEMPORARY TABLE __temp__tag AS SELECT uuid, user_id, parent_id, name, path, created_at, updated_at FROM "tag"');
    $this->addSql('DROP TABLE "tag"');
    $this->addSql('CREATE TABLE "tag" (uuid CHAR(36) NOT NULL, user_id VARCHAR(36) NOT NULL, parent_id VARCHAR(36) DEFAULT NULL, name VARCHAR(50) NOT NULL, path CLOB NOT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, PRIMARY KEY(uuid), CONSTRAINT FK_389B783A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (uuid) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_389B783727ACA70 FOREIGN KEY (parent_id) REFERENCES "tag" (uuid) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE)');
    $this->addSql('INSERT INTO "tag" (uuid, user_id, parent_id, name, path, created_at, updated_at) SELECT uuid, user_id, parent_id, name, path, created_at, updated_at FROM __temp__tag');
    $this->addSql('DROP TABLE __temp__tag');
    $this->addSql('CREATE INDEX IDX_389B783A76ED395 ON "tag" (user_id)');
    $this->addSql('CREATE INDEX IDX_389B783727ACA70 ON "tag" (parent_id)');
    $this->addSql('CREATE INDEX idx_tag_user_name ON "tag" (user_id, name)');
    $this->addSql('CREATE INDEX idx_tag_user_parent ON "tag" (user_id, parent_id)');
    $this->addSql('CREATE INDEX idx_tag_user_path ON "tag" (user_id, path)');
  }

  public function down(Schema $schema): void {
    $this->addSql('CREATE TEMPORARY TABLE __temp__image_to_tag AS SELECT image_id, tag_id FROM image_to_tag');
    $this->addSql('DROP TABLE image_to_tag');
    $this->addSql('CREATE TABLE image_to_tag (image_id CHAR(36) NOT NULL, tag_id CHAR(36) NOT NULL, PRIMARY KEY(image_id, tag_id), CONSTRAINT FK_FAA821453DA5256D FOREIGN KEY (image_id) REFERENCES "image" (uuid) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_FAA82145BAD26311 FOREIGN KEY (tag_id) REFERENCES "tag" (uuid) NOT DEFERRABLE INITIALLY IMMEDIATE)');
    $this->addSql('INSERT INTO image_to_tag (image_id, tag_id) SELECT image_id, tag_id FROM __temp__image_to_tag');
    $this->addSql('DROP TABLE __temp__image_to_tag');
    $this->addSql('CREATE INDEX IDX_FAA821453DA5256D ON image_to_tag (image_id)');
    $this->addSql('CREATE INDEX IDX_FAA82145BAD26311 ON image_to_tag (tag_id)');

    $this->addSql('CREATE TEMPORARY TABLE __temp__tag AS SELECT uuid, user_id, parent_id, name, path, created_at, updated_at FROM "tag"');
    $this->addSql('DROP TABLE "tag"');
    $this->addSql('CREATE TABLE "tag" (uuid CHAR(36) NOT NULL, user_id VARCHAR(36) NOT NULL, parent_id VARCHAR(36) DEFAULT NULL, name VARCHAR(50) NOT NULL, path CLOB NOT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, PRIMARY KEY(uuid), CONSTRAINT FK_389B783A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (uuid) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_389B783727ACA70 FOREIGN KEY (parent_id) REFERENCES "tag" (uuid) NOT DEFERRABLE INITIALLY IMMEDIATE)');
    $this->addSql('INSERT INTO "tag" (uuid, user_id, parent_id, name, path, created_at, updated_at) SELECT uuid, user_id, parent_id, name, path, created_at, updated_at FROM __temp__tag');
    $this->addSql('DROP TABLE __temp__tag');
    $this->addSql('CREATE INDEX IDX_389B783A76ED395 ON "tag" (user_id)');
    $this->addSql('CREATE INDEX IDX_389B783727ACA70 ON "tag" (parent_id)');
    $this->addSql('CREATE INDEX idx_tag_user_name ON "tag" (user_id, name)');
    $this->addSql('CREATE INDEX idx_tag_user_parent ON "tag" (user_id, parent_id)');
    $this->addSql('CREATE INDEX idx_tag_user_path ON "tag" (user_id, path)');
  }
}
