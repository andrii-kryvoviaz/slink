<?php

declare(strict_types=1);

namespace Slink\Shared\Infrastructure\Persistence\Doctrine\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Slink\Shared\Domain\ValueObject\EscapedString;

final class Version20260126000000 extends AbstractMigration {
  public function getDescription(): string {
    return 'Sanitize existing user-generated content to escape HTML entities for XSS protection';
  }

  public function up(Schema $schema): void {
    $this->sanitizeTable('image', 'uuid', ['description']);
    $this->sanitizeTable('collection', 'uuid', ['name', 'description']);
    $this->sanitizeTable('comment', 'uuid', ['content']);
    $this->sanitizeTable('user', 'uuid', ['username', 'display_name']);
  }

  public function down(Schema $schema): void {
  }

  /**
   * @param array<string> $columns
   */
  private function sanitizeTable(string $table, string $idColumn, array $columns): void {
    $columnList = implode(', ', array_merge([$idColumn], $columns));
    $rows = $this->connection->fetchAllAssociative(
      sprintf('SELECT %s FROM "%s"', $columnList, $table)
    );

    foreach ($rows as $row) {
      $updates = [];
      $params = [];
      
      foreach ($columns as $column) {
        $value = $row[$column];
        if ($value === null || $value === '') {
          continue;
        }
        
        $sanitized = EscapedString::fromString($value)->getValue();
        
        if ($sanitized !== $value) {
          $updates[] = sprintf('%s = ?', $column);
          $params[] = $sanitized;
        }
      }
      
      if (!empty($updates)) {
        $params[] = $row[$idColumn];
        $this->connection->executeStatement(
          sprintf('UPDATE "%s" SET %s WHERE %s = ?', $table, implode(', ', $updates), $idColumn),
          $params
        );
      }
    }
  }
}
