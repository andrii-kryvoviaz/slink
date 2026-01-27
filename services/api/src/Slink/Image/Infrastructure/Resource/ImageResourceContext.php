<?php

declare(strict_types=1);

namespace Slink\Image\Infrastructure\Resource;

use Slink\Shared\Infrastructure\Resource\AbstractResourceContext;

final readonly class ImageResourceContext extends AbstractResourceContext {
  /**
   * @param array<string> $groups
   * @param array<string> $imageIds
   */
  public function __construct(
    array          $groups = ['public'],
    public array   $imageIds = [],
    public ?string $viewerUserId = null,
  ) {
    parent::__construct($groups);
  }

  /**
   * @param iterable<string> $imageIds
   */
  public function withImageIds(iterable $imageIds): ImageResourceContext {
    return new ImageResourceContext(
      $this->getGroups(),
      iterator_to_array($imageIds),
      $this->viewerUserId,
    );
  }
}
