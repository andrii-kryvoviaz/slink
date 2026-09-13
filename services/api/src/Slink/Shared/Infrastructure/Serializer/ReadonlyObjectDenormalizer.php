<?php

declare(strict_types=1);

namespace Slink\Shared\Infrastructure\Serializer;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

final class ReadonlyObjectDenormalizer implements DenormalizerInterface {
  public function __construct(
    #[Autowire(service: 'serializer.normalizer.property')]
    private readonly DenormalizerInterface $propertyNormalizer,
  ) {}

  /**
   * @param array<string, mixed> $context
   */
  public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): mixed {
    return $this->propertyNormalizer->denormalize($data, $type, $format, $context);
  }

  /**
   * @param array<string, mixed> $context
   */
  public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool {
    return class_exists($type) && (new \ReflectionClass($type))->isReadOnly();
  }

  /**
   * @return array<string, bool>
   */
  public function getSupportedTypes(?string $format): array {
    return ['*' => false];
  }
}
