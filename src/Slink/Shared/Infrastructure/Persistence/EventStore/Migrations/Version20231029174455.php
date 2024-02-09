<?php

declare(strict_types=1);

namespace Slink\Shared\Infrastructure\Persistence\EventStore\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231029174455 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
      $this->addSql('CREATE TABLE IF NOT EXISTS event_store (
        id SERIAL PRIMARY KEY,
        event_id UUID NOT NULL,
        aggregate_root_id UUID NOT NULL,
        version INT NULL,
        payload varchar(16001) NOT NULL,
        CONSTRAINT reconstitution UNIQUE (aggregate_root_id, version)
      );
');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
      $this->addSql('DROP TABLE `event_store`');
    }
}
