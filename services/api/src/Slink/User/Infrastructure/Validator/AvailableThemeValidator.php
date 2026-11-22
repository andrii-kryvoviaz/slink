<?php

declare(strict_types=1);

namespace Slink\User\Infrastructure\Validator;

use Slink\Settings\Application\Service\SettingsService;
use Slink\Settings\Domain\Provider\ConfigurationProviderInterface;
use Slink\Settings\Domain\ValueObject\Customization\CustomizationSettings;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class AvailableThemeValidator extends ConstraintValidator {
  /**
   * @param ConfigurationProviderInterface<SettingsService> $configurationProvider
   */
  public function __construct(
    private readonly ConfigurationProviderInterface $configurationProvider,
  ) {
  }

  public function validate(mixed $value, Constraint $constraint): void {
    if (!$constraint instanceof AvailableTheme) {
      throw new UnexpectedTypeException($constraint, AvailableTheme::class);
    }

    if ($value === null) {
      return;
    }

    $availableThemes = CustomizationSettings::availableThemesFrom($this->configurationProvider->all()['customization'] ?? []);

    if (!in_array($value, $availableThemes, true)) {
      $this->context->buildViolation($constraint->message)->addViolation();
    }
  }
}
