<?php

declare(strict_types=1);

namespace Slink\Comment\Infrastructure\Serializer;

use Slink\Comment\Domain\Service\CommentEditPolicy;
use Slink\Comment\Infrastructure\ReadModel\View\CommentView;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final readonly class CommentNormalizer implements NormalizerInterface {
  public function __construct(
    #[Autowire(service: 'serializer.normalizer.object')]
    private NormalizerInterface $normalizer,
  ) {
  }

  /**
   * @param CommentView $data
   * @param array<string, mixed> $context
   * @return array<string, mixed>
   */
  public function normalize(mixed $data, ?string $format = null, array $context = []): array {
    /** @var array<string, mixed> $normalized */
    $normalized = $this->normalizer->normalize($data, $format, $context);

    if (!$data->isDeleted()) {
      $normalized['canEdit'] = CommentEditPolicy::canEdit($data->getCreatedAt());
    } else {
      $normalized['canEdit'] = false;
    }

    return $normalized;
  }

  public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool {
    return $data instanceof CommentView;
  }

  /**
   * @return array<class-string, bool>
   */
  public function getSupportedTypes(?string $format): array {
    return [
      CommentView::class => true,
    ];
  }
}
