<?php

declare(strict_types=1);

namespace Slink\Image\Application\Command\DeleteImage;

use Slink\Image\Domain\Enum\ImageAccess;
use Slink\Image\Domain\Repository\ImageStoreRepositoryInterface;
use Slink\Shared\Application\Command\CommandHandlerInterface;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Shared\Infrastructure\Exception\NotFoundException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

final readonly class DeleteImageHandler implements CommandHandlerInterface {
  public function __construct(
    private ImageStoreRepositoryInterface $imageRepository,
    private AuthorizationCheckerInterface $access,
  ) {
  }

  public function __invoke(
    DeleteImageCommand $command,
    string $userId,
    string $id
  ): void {
    $image = $this->imageRepository->get(ID::fromString($id));

    if (!$image->aggregateRootVersion()) {
      throw new NotFoundException();
    }

    if (!$this->access->isGranted(ImageAccess::Delete, $image)) {
      throw new AccessDeniedException();
    }

    $image->delete(ID::fromString($userId), $command->preserveOnDisk());

    $this->imageRepository->store($image);
  }
}
