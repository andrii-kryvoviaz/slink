<?php

declare(strict_types=1);

namespace Slink\Shared\Infrastructure\Serializer;

use Slink\Shared\Domain\ValueObject\AbstractStringValueObject;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class StringValueObjectNormalizer implements NormalizerInterface {
  /**
   * @param AbstractStringValueObject $data
   * @param array<string, mixed> $context
   */
  public function normalize(mixed $data, ?string $format = null, array $context = []): string {
    return $data->toString();
  }

  public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool {
    return $data instanceof AbstractStringValueObject;
  }

  /**
   * @return array<class-string, bool>
   */
  public function getSupportedTypes(?string $format): array {
    return [
      AbstractStringValueObject::class => true,
    ];
  }
}
