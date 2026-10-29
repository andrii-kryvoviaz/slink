<?php

declare(strict_types=1);

namespace Tests\Integration\Http\Registration;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\Integration\Http\HttpTestCase;

#[Group('sc-1143')]
final class SignupUsernameRulesTest extends HttpTestCase {
  private const string INVALID_CHARACTERS = 'Username can only contain lowercase letters, numbers, underscores, hyphens, and periods.';
  private const string CONSECUTIVE_SPECIAL_CHARACTERS = 'Username cannot contain consecutive special characters.';

  /**
   * @return array<string, array{string, string}>
   */
  public static function provideRejectedUsernames(): array {
    return [
      'Too short' => ['jo', 'Username must be at least 3 characters long.'],
      'Too long' => [str_repeat('a', 31), 'Username must be at most 30 characters long.'],
      'Uppercase letter' => ['John', self::INVALID_CHARACTERS],
      'Space' => ['john doe', self::INVALID_CHARACTERS],
      'Email shaped' => ['user@corp.com', self::INVALID_CHARACTERS],
      'Two underscores' => ['john__doe', self::CONSECUTIVE_SPECIAL_CHARACTERS],
      'Period then hyphen' => ['john.-doe', self::CONSECUTIVE_SPECIAL_CHARACTERS],
      'Capitalised reserved name fails charset first' => ['Anonymous', self::INVALID_CHARACTERS],
      'Reserved name' => ['anonymous', '`anonymous` is a reserved username.'],
      'Charset wins over consecutive special characters' => ['John__doe', self::INVALID_CHARACTERS],
      'Length counted in characters' => ['éé', 'Username must be at least 3 characters long.'],
      'Charset wins for long multibyte value' => [str_repeat('é', 16), self::INVALID_CHARACTERS],
    ];
  }

  /**
   * @return array<string, array{string}>
   */
  public static function provideAcceptedUsernames(): array {
    return [
      'Dotted' => ['john.doe'],
      'Underscore, hyphen and digit' => ['john_doe-1'],
      'Maximum length lowercase' => [str_repeat('a', 30)],
    ];
  }

  #[Test]
  #[DataProvider('provideRejectedUsernames')]
  public function signupRejectsUsernameWithTodaysMessage(string $username, string $message): void {
    $this->allowRegistration();

    $status = $this->signUp('rejected@local.test', $username);

    self::assertContains($status, [400, 422], (string) $this->client->getResponse()->getContent());
    self::assertSame([$message], $this->usernameViolationMessages());
  }

  #[Test]
  #[DataProvider('provideAcceptedUsernames')]
  public function signupCreatesUserWithValidUsername(string $username): void {
    $this->allowRegistration();

    $status = $this->signUp('accepted@local.test', $username);

    self::assertContains($status, [200, 201, 202, 204], (string) $this->client->getResponse()->getContent());
    self::assertNotSame('', $this->login($username, self::PASSWORD));
  }

  /**
   * @return list<string>
   */
  private function usernameViolationMessages(): array {
    $error = $this->responsePayload()['error'] ?? [];
    $violations = \is_array($error) ? ($error['violations'] ?? []) : [];

    return \array_values(\array_map(
      static fn(array $violation): string => (string) ($violation['message'] ?? ''),
      \array_filter(
        \is_array($violations) ? $violations : [],
        static fn(mixed $violation): bool => \is_array($violation) && ($violation['property'] ?? null) === 'username',
      ),
    ));
  }
}
