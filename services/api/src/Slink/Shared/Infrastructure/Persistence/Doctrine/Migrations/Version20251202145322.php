<?php

declare(strict_types=1);

namespace Slink\Shared\Infrastructure\Persistence\Doctrine\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251202145322 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE "notification" (uuid CHAR(36) NOT NULL --(DC2Type:uuid)
        , user_id CHAR(36) NOT NULL --(DC2Type:uuid)
        , reference_id CHAR(36) NOT NULL --(DC2Type:uuid)
        , related_comment_id CHAR(36) DEFAULT NULL --(DC2Type:uuid)
        , actor_id CHAR(36) DEFAULT NULL --(DC2Type:uuid)
        , type VARCHAR(50) NOT NULL, created_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , is_read BOOLEAN NOT NULL, read_at DATETIME DEFAULT NULL --(DC2Type:datetime_immutable)
        , PRIMARY KEY(uuid), CONSTRAINT FK_BF5476CAA76ED395 FOREIGN KEY (user_id) REFERENCES "user" (uuid) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_BF5476CA1645DEA9 FOREIGN KEY (reference_id) REFERENCES "image" (uuid) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_BF5476CA72A475A3 FOREIGN KEY (related_comment_id) REFERENCES "comment" (uuid) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_BF5476CA10DAF24A FOREIGN KEY (actor_id) REFERENCES "user" (uuid) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_BF5476CAA76ED395 ON "notification" (user_id)');
        $this->addSql('CREATE INDEX IDX_BF5476CA1645DEA9 ON "notification" (reference_id)');
        $this->addSql('CREATE INDEX IDX_BF5476CA72A475A3 ON "notification" (related_comment_id)');
        $this->addSql('CREATE INDEX IDX_BF5476CA10DAF24A ON "notification" (actor_id)');
        $this->addSql('CREATE INDEX idx_notification_user_read_created ON "notification" (user_id, is_read, created_at)');
        $this->addSql('CREATE INDEX idx_notification_user_created ON "notification" (user_id, created_at)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE "notification"');
    }
}
