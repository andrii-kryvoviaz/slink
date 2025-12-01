<?php

declare(strict_types=1);

namespace Slink\Comment\Application\Query\GetCommentsByImage;

use Slink\Comment\Domain\Repository\CommentRepositoryInterface;
use Slink\Shared\Application\Http\Collection;
use Slink\Shared\Application\Http\Item;
use Slink\Shared\Application\Query\QueryHandlerInterface;

final readonly class GetCommentsByImageHandler implements QueryHandlerInterface {
  public function __construct(
    private CommentRepositoryInterface $commentRepository,
  ) {
  }

  public function __invoke(GetCommentsByImageQuery $query): Collection {
    $paginator = $this->commentRepository->findByImageId(
      $query->getImageId(),
      $query->getPage(),
      $query->getLimit(),
    );

    $comments = iterator_to_array($paginator);
    $total = $paginator->count();

    $items = array_map(fn($comment) => Item::fromEntity($comment), $comments);

    return new Collection(
      $query->getPage(),
      $query->getLimit(),
      $total,
      $items,
    );
  }
}
