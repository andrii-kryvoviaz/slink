<?php

declare(strict_types=1);

namespace Slink\Shared\Infrastructure\Persistence\Doctrine\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231101221641 extends AbstractMigration
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
            $this->addSql('ALTER TABLE image ADD original_name VARCHAR(255) NOT NULL');
            $this->addSql('ALTER TABLE image ADD file_date_time TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL');
            $this->addSql('ALTER TABLE image ADD file_size INT NOT NULL');
            $this->addSql('ALTER TABLE image ADD mime_type VARCHAR(255) NOT NULL');
            $this->addSql('ALTER TABLE image ADD width INT NOT NULL');
            $this->addSql('ALTER TABLE image ADD height INT NOT NULL');
            $this->addSql('ALTER TABLE image ADD is_color BOOLEAN NOT NULL');
            $this->addSql('COMMENT ON COLUMN image.file_date_time IS \'(DC2Type:datetime_immutable)\'');
        }
    }

    public function down(Schema $schema): void
    {
        $platform = $this->connection->getDatabasePlatform();
        
        if($platform instanceof \Doctrine\DBAL\Platforms\PostgreSQLPlatform) {
            // this down() migration is auto-generated, please modify it to your needs
            $this->addSql('CREATE SCHEMA public');
            $this->addSql('ALTER TABLE "image" DROP original_name');
            $this->addSql('ALTER TABLE "image" DROP file_date_time');
            $this->addSql('ALTER TABLE "image" DROP file_size');
            $this->addSql('ALTER TABLE "image" DROP mime_type');
            $this->addSql('ALTER TABLE "image" DROP width');
            $this->addSql('ALTER TABLE "image" DROP height');
            $this->addSql('ALTER TABLE "image" DROP is_color');
        }
    }
}
