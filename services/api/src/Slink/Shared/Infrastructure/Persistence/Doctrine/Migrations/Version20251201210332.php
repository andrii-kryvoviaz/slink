<?php

declare(strict_types=1);

namespace Slink\Shared\Infrastructure\Persistence\Doctrine\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251201210332 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create comment table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE "comment" (uuid CHAR(36) NOT NULL --(DC2Type:uuid)
        , referenced_comment_id CHAR(36) DEFAULT NULL --(DC2Type:uuid)
        , image_id CHAR(36) NOT NULL --(DC2Type:uuid)
        , user_id CHAR(36) NOT NULL --(DC2Type:uuid)
        , content CLOB NOT NULL, created_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , updated_at DATETIME DEFAULT NULL --(DC2Type:datetime_immutable)
        , deleted_at DATETIME DEFAULT NULL --(DC2Type:datetime_immutable)
        , PRIMARY KEY(uuid), CONSTRAINT FK_9474526C18AE5D55 FOREIGN KEY (referenced_comment_id) REFERENCES "comment" (uuid) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_9474526C3DA5256D FOREIGN KEY (image_id) REFERENCES "image" (uuid) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_9474526CA76ED395 FOREIGN KEY (user_id) REFERENCES "user" (uuid) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_9474526C3DA5256D ON "comment" (image_id)');
        $this->addSql('CREATE INDEX idx_comment_image_created ON "comment" (image_id, created_at)');
        $this->addSql('CREATE INDEX idx_comment_user ON "comment" (user_id)');
        $this->addSql('CREATE INDEX idx_comment_referenced ON "comment" (referenced_comment_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE "comment"');
    }
}
