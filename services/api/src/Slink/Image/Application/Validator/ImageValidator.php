<?php

declare(strict_types=1);

namespace Slink\Image\Application\Validator;

use Slink\Settings\Application\Service\SettingsService;
use Slink\Settings\Domain\Provider\ConfigurationProviderInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\ImageValidator as BaseImageValidator;

#[AutoconfigureTag(name: 'validator.constraint_validator', attributes: ['alias' => BaseImageValidator::class])]
final class ImageValidator extends BaseImageValidator {
  /**
   * @param ConfigurationProviderInterface<SettingsService> $configurationProvider
   * @param ParameterBagInterface $parameterBag
   */
  public function __construct(
    private readonly ConfigurationProviderInterface $configurationProvider,
    private readonly ParameterBagInterface          $parameterBag
  ) {
  }
  
  /**
   * @param mixed $value
   * @param Image $constraint
   * @return void
   */
  #[\Override]
  public function validate(mixed $value, Constraint $constraint): void {
    $constraint->maxSize = $this->configurationProvider->get('image.maxSize');
    $constraint->mimeTypes = (array) $this->parameterBag->get('supported_image_formats');
    
    parent::validate($value, $constraint);
  }
}