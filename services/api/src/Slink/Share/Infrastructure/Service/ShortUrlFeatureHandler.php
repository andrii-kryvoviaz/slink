<?php

declare(strict_types=1);

namespace Slink\Share\Infrastructure\Service;

use Slink\Settings\Domain\Provider\ConfigurationProviderInterface;
use Slink\Share\Domain\Service\ShareFeatureHandlerInterface;
use Slink\Share\Domain\Service\ShortCodeGeneratorInterface;
use Slink\Share\Domain\ValueObject\ShareContext;
use Slink\Shared\Domain\ValueObject\ID;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('share.feature_handler', ['priority' => 100])]
final readonly class ShortUrlFeatureHandler implements ShareFeatureHandlerInterface {
  public function __construct(
    private ConfigurationProviderInterface $configurationProvider,
    private ShortCodeGeneratorInterface $shortCodeGenerator,
  ) {}

  public function supports(ShareContext $context): bool {
    return $this->configurationProvider->get('share.enableUrlShortening') ?? true;
  }

  public function enhance(ShareContext $context): ShareContext {
    return $context->withShortUrl(
      ID::generate(),
      $this->shortCodeGenerator->generate(),
    );
  }
}
