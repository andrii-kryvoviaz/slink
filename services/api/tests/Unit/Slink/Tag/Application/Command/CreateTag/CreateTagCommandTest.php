<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Tag\Application\Command\CreateTag;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Tag\Application\Command\CreateTag\CreateTagCommand;

final class CreateTagCommandTest extends TestCase {

  #[Test]
  public function itCreatesCommandWithValidName(): void {
    $command = new CreateTagCommand('valid-name');
    
    $this->assertEquals('valid-name', $command->getName());
    $this->assertNull($command->getParentId());
  }

  #[Test]
  public function itCreatesCommandWithParentId(): void {
    $parentId = 'parent-id-123';
    $command = new CreateTagCommand('child-name', $parentId);
    
    $this->assertEquals('child-name', $command->getName());
    $this->assertEquals($parentId, $command->getParentId());
  }

  #[Test]
  #[DataProvider('validTagNameProvider')]
  public function itAcceptsValidTagNames(string $name): void {
    $command = new CreateTagCommand($name);
    
    $this->assertEquals($name, $command->getName());
  }

  #[Test]
  public function itAcceptsValidUuidAsParentId(): void {
    $validUuid = '550e8400-e29b-41d4-a716-446655440000';
    $command = new CreateTagCommand('test-tag', $validUuid);
    
    $this->assertEquals($validUuid, $command->getParentId());
  }

  #[Test]
  public function itAcceptsNullParentId(): void {
    $command = new CreateTagCommand('root-tag', null);
    
    $this->assertNull($command->getParentId());
  }

  /**
   * @return array<string, array<string>>
   */
  public static function validTagNameProvider(): array {
    return [
      'single character' => ['a'],
      'letters only' => ['tag'],
      'numbers only' => ['123'],
      'with hyphens' => ['tag-name'],
      'with underscores' => ['tag_name'],
      'mixed characters' => ['Tag_Name-123'],
      'uppercase letters' => ['TAG'],
      'max length' => [str_repeat('a', 50)],
    ];
  }
}