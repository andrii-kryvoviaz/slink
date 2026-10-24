<?php

declare(strict_types=1);

namespace Slink\Image\Infrastructure\Serializer;

use Slink\Image\Domain\Service\PublicImageUrlBuilderInterface;
use Slink\Image\Domain\ValueObject\ImageAttributes;
use Slink\Image\Domain\ValueObject\ImageMetadata;
use Slink\Image\Infrastructure\ReadModel\View\ImageView;
use Slink\User\Domain\ValueObject\GuestUser;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final readonly class ImageNormalizer implements NormalizerInterface {
  public function __construct(
    #[Autowire(service: 'serializer.normalizer.object')]
    private NormalizerInterface $normalizer,
    private PublicImageUrlBuilderInterface $urlBuilder,
  ) {
  }

  /**
   * @param ImageView|ImageAttributes|ImageMetadata|GuestUser $data
   * @param array<string, mixed> $context
   * @return array<string, mixed>|string|int|float|bool|\ArrayObject|null
   */
  public function normalize(mixed $data, ?string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null {
    $normalized = $this->normalizer->normalize($data, $format, $context);
    if (!$data instanceof ImageView || !is_array($normalized)) {
      return $normalized;
    }

    $groups = $context['groups'] ?? [];
    if (is_string($groups)) {
      $groups = [$groups];
    }
    if (!in_array('public', $groups, true) && !in_array('*', $groups, true)) {
      return $normalized;
    }

    $normalized['url'] = $this->urlBuilder->build($data->getUuid(), $data->getFileName());

    return $normalized;
  }

  public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool {
    return !empty($context['groups']) && (
      $data instanceof ImageView
      || $data instanceof ImageAttributes
      || $data instanceof ImageMetadata
      || $data instanceof GuestUser
    );
  }

  /** @return array<class-string, bool> */
  public function getSupportedTypes(?string $format): array {
    return [
      ImageView::class => false,
      ImageAttributes::class => false,
      ImageMetadata::class => false,
      GuestUser::class => false,
    ];
  }
}
