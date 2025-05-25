<?php

declare(strict_types=1);

namespace Slink\Shared\Infrastructure\Persistence\Doctrine\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240501225526 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE user_to_role (user_id CHAR(36) NOT NULL --(DC2Type:uuid)
        , role VARCHAR(255) NOT NULL, PRIMARY KEY(user_id, role), CONSTRAINT FK_E88A85AFA76ED395 FOREIGN KEY (user_id) REFERENCES "user" (uuid) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_E88A85AF57698A6A FOREIGN KEY (role) REFERENCES "user_role" (role) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_E88A85AFA76ED395 ON user_to_role (user_id)');
        $this->addSql('CREATE INDEX IDX_E88A85AF57698A6A ON user_to_role (role)');
        $this->addSql('CREATE TABLE "user_role" (role VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(role))');
        
        $this->addSql('INSERT INTO "user_role" (role, name) VALUES (\'ROLE_USER\', \'User\')');
        $this->addSql('INSERT INTO "user_role" (role, name) VALUES (\'ROLE_ADMIN\', \'Administrator\')');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE user_to_role');
        $this->addSql('DROP TABLE "user_role"');
    }
}
