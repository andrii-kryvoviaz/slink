<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\User\Infrastructure\Validator;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Slink\Settings\Domain\Provider\ConfigurationProviderInterface;
use Slink\Settings\Domain\ValueObject\Customization\Theme;
use Slink\User\Infrastructure\Validator\AvailableTheme;
use Slink\User\Infrastructure\Validator\AvailableThemeValidator;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

final class AvailableThemeValidatorTest extends TestCase {
  #[Test]
  #[TestWith([null])]
  #[TestWith(['default'])]
  #[TestWith(['catppuccin'])]
  #[TestWith(['ocean'])]
  public function itAcceptsAvailableThemes(?string $theme): void {
    $this->validate($theme, 0);
  }

  #[Test]
  #[TestWith([''])]
  #[TestWith(['solarized'])]
  #[TestWith(['nord'])]
  public function itRejectsUnknownAndHiddenThemes(string $theme): void {
    $this->validate($theme, 1);
  }

  #[Test]
  #[TestWith(['default'])]
  #[TestWith(['ocean'])]
  public function itStillValidatesThemesWhenTheConfiguredDefaultThemeIsInvalid(string $theme): void {
    $this->validate($theme, 0, 'ghost');
  }

  #[Test]
  public function itRejectsForeignConstraint(): void {
    $this->expectException(UnexpectedTypeException::class);

    new AvailableThemeValidator($this->createStub(ConfigurationProviderInterface::class))
      ->validate('default', new NotBlank());
  }

  private function validate(?string $theme, int $violations, string $defaultTheme = 'default'): void {
    $provider = $this->createStub(ConfigurationProviderInterface::class);
    $provider->method('all')->willReturn([
      'customization' => [
        'defaultTheme' => $defaultTheme,
        'hiddenThemes' => ['nord'],
        'themes' => [[
          'id' => 'ocean',
          'name' => 'Ocean',
          'light' => array_fill_keys(Theme::COLORS, '#ffffff'),
          'dark' => array_fill_keys(Theme::COLORS, '#000000'),
        ]],
      ],
    ]);

    $context = $this->createMock(ExecutionContextInterface::class);
    $context->expects($this->exactly($violations))
      ->method('buildViolation')
      ->with('Invalid display theme.')
      ->willReturn($this->createStub(ConstraintViolationBuilderInterface::class));

    $validator = new AvailableThemeValidator($provider);
    $validator->initialize($context);
    $validator->validate($theme, new AvailableTheme());
  }
}
