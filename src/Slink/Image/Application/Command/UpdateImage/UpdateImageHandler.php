<?php

declare(strict_types=1);

namespace Slink\Image\Application\Command\UpdateImage;

use Slink\Image\Domain\Repository\ImageStoreRepositoryInterface;
use Slink\Shared\Application\Command\CommandHandlerInterface;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Shared\Infrastructure\Exception\NotFoundException;
use Slink\User\Infrastructure\Auth\JwtUser;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

final readonly class UpdateImageHandler implements CommandHandlerInterface {
  
  public function __construct(
    private ImageStoreRepositoryInterface $imageRepository,
  ) {
  }
  
  /**
   * @throws NotFoundException
   */
  public function __invoke(
    UpdateImageCommand $command,
    ?JwtUser $user,
    string $id
  ): void {
    $image = $this->imageRepository->get(ID::fromString($id));
    $userId = ID::fromString($user?->getIdentifier() ?? '');
    
    if (!$image->aggregateRootVersion()) {
      throw new NotFoundException();
    }
    
    if($user && !$image->getUserId()->equals($userId)) {
      throw new AccessDeniedException();
    }
    
    $attributes = clone $image->getAttributes()
      ->withDescription($command->getDescription())
      ->withIsPublic($command->getIsPublic());
    
    $image->updateAttributes($attributes);
    
    $this->imageRepository->store($image);
  }
}