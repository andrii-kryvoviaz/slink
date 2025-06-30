<?php

declare(strict_types=1);

namespace Unit\Slink\User\Domain\Enum;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\User\Domain\Enum\UserStatus;

final class UserStatusTest extends TestCase {

  /**
   * @return array<string, array{UserStatus, bool}>
   */
  public static function provideActiveStatusChecks(): array {
    return [
      'Active is active' => [UserStatus::Active, true],
      'Inactive is not active' => [UserStatus::Inactive, false],
      'Suspended is not active' => [UserStatus::Suspended, false],
      'Banned is not active' => [UserStatus::Banned, false],
      'Deleted is not active' => [UserStatus::Deleted, false],
    ];
  }

  /**
   * @return array<string, array{UserStatus, bool}>
   */
  public static function provideBannedStatusChecks(): array {
    return [
      'Active is not banned' => [UserStatus::Active, false],
      'Inactive is not banned' => [UserStatus::Inactive, false],
      'Suspended is not banned' => [UserStatus::Suspended, false],
      'Banned is banned' => [UserStatus::Banned, true],
      'Deleted is not banned' => [UserStatus::Deleted, false],
    ];
  }

  /**
   * @return array<string, array{UserStatus, bool}>
   */
  public static function provideDeletedStatusChecks(): array {
    return [
      'Active is not deleted' => [UserStatus::Active, false],
      'Inactive is not deleted' => [UserStatus::Inactive, false],
      'Suspended is not deleted' => [UserStatus::Suspended, false],
      'Banned is not deleted' => [UserStatus::Banned, false],
      'Deleted is deleted' => [UserStatus::Deleted, true],
    ];
  }

  /**
   * @return array<string, array{string, UserStatus}>
   */
  public static function provideFromStringData(): array {
    return [
      'Active status' => ['active', UserStatus::Active],
      'Inactive status' => ['inactive', UserStatus::Inactive],
      'Suspended status' => ['suspended', UserStatus::Suspended],
      'Banned status' => ['banned', UserStatus::Banned],
      'Deleted status' => ['deleted', UserStatus::Deleted],
    ];
  }

  /**
   * @return array<string, array{UserStatus, bool}>
   */
  public static function provideInactiveStatusChecks(): array {
    return [
      'Active is not inactive' => [UserStatus::Active, false],
      'Inactive is inactive' => [UserStatus::Inactive, true],
      'Suspended is not inactive' => [UserStatus::Suspended, false],
      'Banned is not inactive' => [UserStatus::Banned, false],
      'Deleted is not inactive' => [UserStatus::Deleted, false],
    ];
  }

  /**
   * @return array<string, array{UserStatus, bool}>
   */
  public static function provideRestrictedStatusChecks(): array {
    return [
      'Active is not restricted' => [UserStatus::Active, false],
      'Inactive is restricted' => [UserStatus::Inactive, true],
      'Suspended is restricted' => [UserStatus::Suspended, true],
      'Banned is restricted' => [UserStatus::Banned, true],
      'Deleted is restricted' => [UserStatus::Deleted, true],
    ];
  }

  /**
   * @return array<string, array{UserStatus, bool}>
   */
  public static function provideSuspendedStatusChecks(): array {
    return [
      'Active is not suspended' => [UserStatus::Active, false],
      'Inactive is not suspended' => [UserStatus::Inactive, false],
      'Suspended is suspended' => [UserStatus::Suspended, true],
      'Banned is not suspended' => [UserStatus::Banned, false],
      'Deleted is not suspended' => [UserStatus::Deleted, false],
    ];
  }

  #[Test]
  #[DataProvider('provideFromStringData')]
  public function itCanCreateFromString(string $value, UserStatus $expected): void {
    $status = UserStatus::from($value);
    $this->assertSame($expected, $status);
  }

  #[Test]
  #[DataProvider('provideActiveStatusChecks')]
  public function itChecksActiveStatusCorrectly(UserStatus $status, bool $expected): void {
    $this->assertSame($expected, $status->isActive());
  }

  #[Test]
  #[DataProvider('provideBannedStatusChecks')]
  public function itChecksBannedStatusCorrectly(UserStatus $status, bool $expected): void {
    $this->assertSame($expected, $status->isBanned());
  }

  #[Test]
  #[DataProvider('provideDeletedStatusChecks')]
  public function itChecksDeletedStatusCorrectly(UserStatus $status, bool $expected): void {
    $this->assertSame($expected, $status->isDeleted());
  }

  #[Test]
  #[DataProvider('provideInactiveStatusChecks')]
  public function itChecksInactiveStatusCorrectly(UserStatus $status, bool $expected): void {
    $this->assertSame($expected, $status->isInactive());
  }

  #[Test]
  #[DataProvider('provideRestrictedStatusChecks')]
  public function itChecksRestrictedStatusCorrectly(UserStatus $status, bool $expected): void {
    $this->assertSame($expected, $status->isRestricted());
  }

  #[Test]
  #[DataProvider('provideSuspendedStatusChecks')]
  public function itChecksSuspendedStatusCorrectly(UserStatus $status, bool $expected): void {
    $this->assertSame($expected, $status->isSuspended());
  }

  #[Test]
  public function itHasCorrectValues(): void {
    $this->assertSame('active', UserStatus::Active->value);
    $this->assertSame('inactive', UserStatus::Inactive->value);
    $this->assertSame('suspended', UserStatus::Suspended->value);
    $this->assertSame('banned', UserStatus::Banned->value);
    $this->assertSame('deleted', UserStatus::Deleted->value);
  }
}
