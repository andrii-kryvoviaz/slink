<?php

declare(strict_types=1);

namespace Unit\Slink\User\Domain\ValueObject;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\User\Domain\ValueObject\Role;

final class RoleTest extends TestCase {

  /**
   * @return array<string, array{string, string}>
   */
  public static function provideRoleStrings(): array {
    return [
      'Basic user role' => ['USER', 'ROLE_USER'],
      'Admin role' => ['ADMIN', 'ROLE_ADMIN'],
      'Moderator role' => ['MODERATOR', 'ROLE_MODERATOR'],
      'Already prefixed user' => ['ROLE_USER', 'ROLE_USER'],
      'Already prefixed admin' => ['ROLE_ADMIN', 'ROLE_ADMIN'],
      'Lowercase user' => ['user', 'ROLE_USER'],
      'Lowercase admin' => ['admin', 'ROLE_ADMIN'],
      'Complex role' => ['super_admin', 'ROLE_SUPER_ADMIN'],
      'Guest role' => ['guest_user', 'ROLE_GUEST_USER'],
      'Simple guest' => ['GUEST', 'ROLE_GUEST'],
      'Numeric role' => ['LEVEL_5', 'ROLE_LEVEL_5'],
      'Mixed case complex' => ['Content_Manager', 'ROLE_CONTENT_MANAGER'],
    ];
  }

  #[Test]
  public function itCreatesRoleFromString(): void {
    $role = Role::fromString('ADMIN');

    $this->assertInstanceOf(Role::class, $role);
    $this->assertSame('ROLE_ADMIN', $role->getRole());
  }

  #[Test]
  public function itCreatesRoleFromStringWithRolePrefix(): void {
    $role = Role::fromString('ROLE_ADMIN');

    $this->assertInstanceOf(Role::class, $role);
    $this->assertSame('ROLE_ADMIN', $role->getRole());
  }

  #[Test]
  #[DataProvider('provideRoleStrings')]
  public function itFormatsRoleCorrectly(string $input, string $expected): void {
    $role = Role::fromString($input);

    $this->assertSame($expected, $role->getRole());
  }

  #[Test]
  public function itHandlesComplexRoleNames(): void {
    $role = Role::fromString('CONTENT_MODERATOR');

    $this->assertSame('ROLE_CONTENT_MODERATOR', $role->getRole());
  }

  #[Test]
  public function itHandlesEmptyRoleGracefully(): void {
    $role = Role::fromString('');

    $this->assertSame('ROLE_', $role->getRole());
  }

  #[Test]
  public function itHandlesLowercaseRoles(): void {
    $role = Role::fromString('admin');

    $this->assertSame('ROLE_ADMIN', $role->getRole());
  }

  #[Test]
  public function itHandlesMixedCaseRoles(): void {
    $role = Role::fromString('AdMiN');

    $this->assertSame('ROLE_ADMIN', $role->getRole());
  }

  #[Test]
  public function itHandlesNumericRoles(): void {
    $role = Role::fromString('LEVEL_1');

    $this->assertSame('ROLE_LEVEL_1', $role->getRole());
  }

  #[Test]
  public function itHandlesRoleEquality(): void {
    $role1 = Role::fromString('ADMIN');
    $role2 = Role::fromString('admin');
    $role3 = Role::fromString('ROLE_ADMIN');
    $role4 = Role::fromString('USER');

    $this->assertSame($role1->getRole(), $role2->getRole());
    $this->assertSame($role1->getRole(), $role3->getRole());
    $this->assertNotSame($role1->getRole(), $role4->getRole());
  }

  #[Test]
  public function itHandlesRoleWithUnderscores(): void {
    $role = Role::fromString('SUPER_ADMIN');

    $this->assertSame('ROLE_SUPER_ADMIN', $role->getRole());
  }
}
