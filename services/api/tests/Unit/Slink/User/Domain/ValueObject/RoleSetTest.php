<?php

declare(strict_types=1);

namespace Unit\Slink\User\Domain\ValueObject;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\User\Domain\ValueObject\Role;
use Slink\User\Domain\ValueObject\RoleSet;

final class RoleSetTest extends TestCase {

  #[Test]
  public function itCreatesEmptyRoleSetWithDefaultRole(): void {
    $roleSet = RoleSet::create();

    $this->assertInstanceOf(RoleSet::class, $roleSet);
    $this->assertTrue($roleSet->contains(Role::fromString('USER')));
    $this->assertEquals(['ROLE_USER'], $roleSet->toArray());
  }

  #[Test]
  public function itCreatesRoleSetWithCustomRoles(): void {
    $roles = [
      Role::fromString('ADMIN'),
      Role::fromString('MODERATOR')
    ];

    $roleSet = RoleSet::create($roles);

    $this->assertTrue($roleSet->contains(Role::fromString('ADMIN')));
    $this->assertTrue($roleSet->contains(Role::fromString('MODERATOR')));
    $this->assertTrue($roleSet->contains(Role::fromString('USER')));
    $this->assertCount(3, $roleSet->getRoles());
  }

  #[Test]
  public function itCreatesRoleSetWithCustomDefaultRoles(): void {
    $roles = [Role::fromString('ADMIN')];
    $defaultRoles = [Role::fromString('GUEST')];

    $roleSet = RoleSet::create($roles, $defaultRoles);

    $this->assertTrue($roleSet->contains(Role::fromString('ADMIN')));
    $this->assertTrue($roleSet->contains(Role::fromString('GUEST')));
    $this->assertFalse($roleSet->contains(Role::fromString('USER')));
  }

  #[Test]
  public function itAddsNewRole(): void {
    $roleSet = RoleSet::create();
    $adminRole = Role::fromString('ADMIN');

    $roleSet->addRole($adminRole);

    $this->assertTrue($roleSet->contains($adminRole));
    $this->assertCount(2, $roleSet->getRoles());
  }

  #[Test]
  public function itDoesNotAddDuplicateRole(): void {
    $roleSet = RoleSet::create();
    $userRole = Role::fromString('USER');

    $initialCount = count($roleSet->getRoles());
    $roleSet->addRole($userRole);

    $this->assertEquals($initialCount, count($roleSet->getRoles()));
    $this->assertTrue($roleSet->contains($userRole));
  }

  #[Test]
  public function itRemovesExistingRole(): void {
    $adminRole = Role::fromString('ADMIN');
    $roleSet = RoleSet::create([$adminRole]);

    $this->assertTrue($roleSet->contains($adminRole));

    $roleSet->removeRole($adminRole);

    $this->assertFalse($roleSet->contains($adminRole));
  }

  #[Test]
  public function itDoesNotFailWhenRemovingNonExistentRole(): void {
    $roleSet = RoleSet::create();
    $nonExistentRole = Role::fromString('NON_EXISTENT');

    $initialCount = count($roleSet->getRoles());
    $roleSet->removeRole($nonExistentRole);

    $this->assertEquals($initialCount, count($roleSet->getRoles()));
  }

  #[Test]
  public function itReturnsCorrectArrayRepresentation(): void {
    $roles = [
      Role::fromString('ADMIN'),
      Role::fromString('MODERATOR')
    ];

    $roleSet = RoleSet::create($roles);
    $roleArray = $roleSet->toArray();

    $this->assertContains('ROLE_ADMIN', $roleArray);
    $this->assertContains('ROLE_MODERATOR', $roleArray);
    $this->assertContains('ROLE_USER', $roleArray);
  }

  #[Test]
  public function itReturnsRolesAsObjectArray(): void {
    $roleSet = RoleSet::create([Role::fromString('ADMIN')]);
    $roles = $roleSet->getRoles();

    $this->assertCount(2, $roles);
    foreach ($roles as $role) {
      $this->assertInstanceOf(Role::class, $role);
    }
  }

  #[Test]
  public function itChecksContainsCorrectly(): void {
    $adminRole = Role::fromString('ADMIN');
    $userRole = Role::fromString('USER');
    $guestRole = Role::fromString('GUEST');

    $roleSet = RoleSet::create([$adminRole]);

    $this->assertTrue($roleSet->contains($adminRole));
    $this->assertTrue($roleSet->contains($userRole));
    $this->assertFalse($roleSet->contains($guestRole));
  }

  #[Test]
  public function itHandlesMultipleOperations(): void {
    $roleSet = RoleSet::create();

    $adminRole = Role::fromString('ADMIN');
    $moderatorRole = Role::fromString('MODERATOR');
    $guestRole = Role::fromString('GUEST');

    $roleSet->addRole($adminRole);
    $roleSet->addRole($moderatorRole);
    $roleSet->addRole($guestRole);

    $this->assertCount(4, $roleSet->getRoles());

    $roleSet->removeRole($moderatorRole);

    $this->assertCount(3, $roleSet->getRoles());
    $this->assertFalse($roleSet->contains($moderatorRole));
    $this->assertTrue($roleSet->contains($adminRole));
    $this->assertTrue($roleSet->contains($guestRole));
  }
}
