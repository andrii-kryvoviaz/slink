<?php

declare(strict_types=1);

namespace Unit\Slink\User\Infrastructure\Service;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\User\Domain\Specification\SignUpUsernameSpecification;
use Slink\User\Domain\ValueObject\DisplayName;
use Slink\User\Infrastructure\Service\UsernameGenerator;
use Symfony\Component\String\Slugger\AsciiSlugger;

final class UsernameGeneratorTest extends TestCase {

  /**
   * @return array<string, array{string, string}>
   */
  public static function provideDisplayNameToUsernameData(): array {
    return [
      'Simple name' => ['John Doe', 'john.doe'],
      'Multiple spaces' => ['  John   Doe  ', 'john.doe'],
      'With hyphen' => ['Jane Smith-Brown', 'jane.smith.brown'],
      'Accented characters' => ['José María', 'jose.maria'],
      'Single name' => ['Madonna', 'madonna'],
      'Numbers in name' => ['User 123', 'user.123'],
      'Mixed case' => ['CamelCase Name', 'camelcase.name'],
      'Apostrophe' => ["O'Neil Smith", 'o.neil.smith'],
      'Adjacent special characters' => ['john__doe', 'john.doe'],
      'Escaped apostrophe' => ['O&apos;Neil', 'o.neil'],
    ];
  }

  /**
   * @return array<string, array{string}>
   */
  public static function provideDisplayNamesWithHtmlSpecialCharacters(): array {
    return [
      'Apostrophe' => ["O'Neil"],
      'Double quotes' => ['"Quoted" Name'],
      'Ampersand' => ['Tom & Jerry'],
      'Angle brackets' => ['<b>Bold</b>'],
      'Encoded entity' => ['O&apos;Neil'],
      'Expands past maximum length' => [str_repeat('½', 15)],
    ];
  }

  #[Test]
  #[DataProvider('provideDisplayNameToUsernameData')]
  public function itGeneratesUsernameFromDisplayName(string $displayName, string $expected): void {
    $this->assertSame($expected, $this->generate($displayName));
  }

  #[Test]
  #[DataProvider('provideDisplayNamesWithHtmlSpecialCharacters')]
  public function itGeneratesSignUpUsernameWithoutEntityText(string $displayName): void {
    $username = (string) $this->generate($displayName);

    $this->assertTrue((new SignUpUsernameSpecification())->isSatisfiedBy($username), $username);
    $this->assertStringNotContainsString('&', $username);
  }

  #[Test]
  public function itGeneratesNothingWhenDisplayNameLeavesNoUsableCharacters(): void {
    $this->assertNull($this->generate('!?!'));
  }

  private function generate(string $displayName): ?string {
    $generator = new UsernameGenerator(new AsciiSlugger('en'));

    return $generator->generate(DisplayName::fromString($displayName))?->toString();
  }
}
