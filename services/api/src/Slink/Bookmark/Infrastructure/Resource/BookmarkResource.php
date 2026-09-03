<?php

declare(strict_types=1);

namespace Slink\Bookmark\Infrastructure\Resource;

use Slink\Bookmark\Infrastructure\ReadModel\View\BookmarkView;
use Slink\Shared\Application\Resource\ResourceInterface;
use Slink\Shared\Domain\ValueObject\Date\DateTime;
use Slink\Shared\Domain\ValueObject\ResourceData;
use Symfony\Component\Serializer\Attribute\Groups;

final class BookmarkResource implements ResourceInterface {
  public function __construct(
    private readonly BookmarkView $bookmark,
    private readonly ResourceData $data = new ResourceData(),
  ) {
  }

  public function getType(): string {
    return BookmarkView::class;
  }

  #[Groups(['public'])]
  public string $id {
    get => $this->bookmark->getId();
  }

  #[Groups(['public'])]
  public DateTime $createdAt {
    get => $this->bookmark->getCreatedAt();
  }

  /** @var array<string, mixed> */
  #[Groups(['public'])]
  public array $image {
    get => $this->data->get('images', $this->bookmark->getImageId()) ?? ['id' => $this->bookmark->getImageId()];
  }
}
