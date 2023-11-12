<?php

declare(strict_types=1);

namespace Slik\Shared\Infrastructure\Persistence\Doctrine\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231103203525 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $platform = $this->connection->getDatabasePlatform();
        
        if($platform instanceof \Doctrine\DBAL\Platforms\PostgreSQLPlatform) {
            // this up() migration is auto-generated, please modify it to your needs
            $this->addSql('ALTER TABLE image ADD time_modified TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
            $this->addSql('ALTER TABLE image DROP original_name');
            $this->addSql('ALTER TABLE image DROP is_color');
            $this->addSql('ALTER TABLE image RENAME COLUMN file_size TO size');
            $this->addSql('ALTER TABLE image RENAME COLUMN file_date_time TO time_created');
            $this->addSql('COMMENT ON COLUMN image.time_modified IS \'(DC2Type:datetime_immutable)\'');
        }
    }

    public function down(Schema $schema): void
    {
        $platform = $this->connection->getDatabasePlatform();
        
        if($platform instanceof \Doctrine\DBAL\Platforms\PostgreSQLPlatform) {
            // this down() migration is auto-generated, please modify it to your needs
            $this->addSql('CREATE SCHEMA public');
            $this->addSql('ALTER TABLE "image" ADD original_name VARCHAR(255) NOT NULL');
            $this->addSql('ALTER TABLE "image" ADD is_color BOOLEAN NOT NULL');
            $this->addSql('ALTER TABLE "image" DROP time_modified');
            $this->addSql('ALTER TABLE "image" RENAME COLUMN time_created TO file_date_time');
            $this->addSql('ALTER TABLE "image" RENAME COLUMN size TO file_size');
        }
    }
}
