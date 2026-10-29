<?php

declare(strict_types=1);

namespace Unit\Slink\User\Domain\Specification;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\User\Domain\Exception\InvalidUsernameException;
use Slink\User\Domain\Specification\SignUpUsernameSpecification;

#[Group('sc-1143')]
final class SignUpUsernameSpecificationTest extends TestCase {
  private const string TOO_SHORT = 'Username must be at least 3 characters long.';
  private const string TOO_LONG = 'Username must be at most 30 characters long.';
  private const string INVALID_CHARACTERS = 'Username can only contain lowercase letters, numbers, underscores, hyphens, and periods.';
  private const string CONSECUTIVE_SPECIAL_CHARACTERS = 'Username cannot contain consecutive special characters.';

  /**
   * @return array<string, array{string}>
   */
  public static function provideAcceptedUsernames(): array {
    return [
      'Dotted' => ['john.doe'],
      'Underscore, hyphen and digit' => ['john_doe-1'],
      'Maximum length lowercase' => [str_repeat('a', 30)],
      'Minimum length' => ['abc'],
      'Separated special characters' => ['a.b_c-d'],
      'Reserved name as a substring' => ['anonymous1'],
    ];
  }

  /**
   * @return array<string, array{string, string}>
   */
  public static function provideRejectedUsernames(): array {
    return [
      'Too short' => ['jo', self::TOO_SHORT],
      'Too long' => [str_repeat('a', 31), self::TOO_LONG],
      'Uppercase letter' => ['John', self::INVALID_CHARACTERS],
      'Space' => ['john doe', self::INVALID_CHARACTERS],
      'Email shaped' => ['user@corp.com', self::INVALID_CHARACTERS],
      'Trailing newline' => ["john\n", self::INVALID_CHARACTERS],
      'Two underscores' => ['john__doe', self::CONSECUTIVE_SPECIAL_CHARACTERS],
      'Period then hyphen' => ['john.-doe', self::CONSECUTIVE_SPECIAL_CHARACTERS],
      'Capitalised reserved name fails charset first' => ['Anonymous', self::INVALID_CHARACTERS],
      'Reserved name' => ['anonymous', '`anonymous` is a reserved username.'],
    ];
  }

  /**
   * @return array<string, array{string}>
   */
  public static function provideRejectedUsernameValues(): array {
    return array_map(static fn(array $data): array => [$data[0]], self::provideRejectedUsernames());
  }

  /**
   * @return array<string, array{string, string}>
   */
  public static function provideRuleOrder(): array {
    return [
      'Length before charset' => ['J@', self::TOO_SHORT],
      'Length before consecutive special characters' => [str_repeat('a', 29) . '__', self::TOO_LONG],
      'Charset before consecutive special characters' => ['John__doe', self::INVALID_CHARACTERS],
      'Length counted in characters, not bytes, below minimum' => ['éé', self::TOO_SHORT],
      'Length counted in characters, not bytes, within maximum' => [str_repeat('é', 16), self::INVALID_CHARACTERS],
    ];
  }

  #[Test]
  #[DataProvider('provideAcceptedUsernames')]
  public function itIsSatisfiedByUsernameMeetingSignUpRules(string $username): void {
    $this->assertTrue((new SignUpUsernameSpecification())->isSatisfiedBy($username));
  }

  #[Test]
  #[DataProvider('provideRejectedUsernameValues')]
  public function itIsNotSatisfiedByUsernameBreakingASignUpRule(string $username): void {
    $this->assertFalse((new SignUpUsernameSpecification())->isSatisfiedBy($username));
  }

  #[Test]
  #[DataProvider('provideRejectedUsernames')]
  public function itRejectsUsernameBreakingASignUpRuleWithThatRulesMessage(string $username, string $message): void {
    $this->expectException(InvalidUsernameException::class);
    $this->expectExceptionMessage($message);

    (new SignUpUsernameSpecification())->validate($username);
  }

  #[Test]
  #[DataProvider('provideRuleOrder')]
  public function itReportsTheFirstFailingSignUpRuleInOrder(string $username, string $message): void {
    $this->expectException(InvalidUsernameException::class);
    $this->expectExceptionMessage($message);

    (new SignUpUsernameSpecification())->validate($username);
  }
}
