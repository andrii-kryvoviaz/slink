<?php

declare(strict_types=1);

namespace Slink\Shared\Infrastructure\Persistence\Doctrine\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260717105609 extends AbstractMigration {
  public function getDescription(): string {
    return 'Scrub identity and revoke access rows for already soft-deleted users';
  }

  public function up(Schema $schema): void {
    $this->addSql('DELETE FROM "oauth_link" WHERE user_id IN (SELECT uuid FROM "user" WHERE status = \'deleted\')');
    $this->addSql('DELETE FROM refresh_token WHERE user_uuid IN (SELECT uuid FROM "user" WHERE status = \'deleted\')');
    $this->addSql('DELETE FROM "api_key" WHERE user_id IN (SELECT uuid FROM "user" WHERE status = \'deleted\')');
    $this->addSql('DELETE FROM user_to_role WHERE user_id IN (SELECT uuid FROM "user" WHERE status = \'deleted\')');
    $this->addSql('UPDATE "user" SET email = \'purged-\' || uuid || \'@purged.local\', username = \'purged_\' || substr(replace(uuid, \'-\', \'\'), 1, 23), display_name = NULL, password = \'!purged!\' WHERE status = \'deleted\'');
  }

  public function down(Schema $schema): void {
  }
}
